# Requerimientos: Planes en $0, Turnos por Día y Notificaciones

**Proyecto:** Turno Médico  
**Fecha:** Febrero 2025  
**Objetivo:** Documento único para validar con el equipo y proceder al desarrollo.

---

## 1. Planes en $0 (sin método de pago)

### 1.1 Contexto

- Los planes con **precio = 0** no deben exigir pago ni comprobante.
- La opción de pago debe quedar **comentada o bloqueada** (no eliminada), para reactivarla en el futuro si se requiere.
- El flujo de aprobación (doctor → administrador para suscripción; doctor para citas) se mantiene; solo se omite la parte de pago cuando el plan es gratuito o la cita no requiere pago.

### 1.2 Suscripción del doctor (planes $0)

| Ámbito | Comportamiento actual | Comportamiento deseado |
|--------|------------------------|-------------------------|
| **Doctor – Mi suscripción** | Siempre muestra datos bancarios y pide comprobante + referencia. | Si el plan elegido tiene **price = 0**: no mostrar método de pago, datos bancarios ni subida de comprobante. Botón tipo "Activar plan" que crea la suscripción en estado `pending` (sin `payment_proof` ni `reference_number`). |
| **Admin – Suscripciones** | Lista todas las suscripciones; para aprobar pide ver comprobante. | Para suscripciones de **planes $0**: no mostrar columna/comprobante de pago; solo mostrar "Aprobar" / "Rechazar". Al aprobar, se marca `payment_status = approved` y se calculan `starts_at` y `ends_at` como hoy. |
| **Backend** | `MembershipController@subscribe` exige `reference_number` y `payment_proof`. | Si `MembershipPlan.price == 0`: no validar ni guardar `payment_proof` ni `reference_number`; crear `Subscription` con `payment_status = 'pending'` y sin archivo. El admin aprueba igual que hoy. |

**Resumen flujo plan $0 (doctor):**  
Doctor elige plan $0 → Activa (sin pago) → Admin aprueba la suscripción → Doctor tiene suscripción activa. Sin formulario de pago en ningún paso.

### 1.3 Citas / turnos de pacientes (sin pago)

| Ámbito | Comportamiento actual | Comportamiento deseado |
|--------|------------------------|-------------------------|
| **Paciente – Reservar turno** | Siempre pide subir comprobante de pago. | **Deshabilitar** la parte de pago (comentada/bloqueada): no pedir comprobante. El paciente solo elige médico, fecha (y en el nuevo modelo: mañana/tarde/noche si aplica) y confirma. Se crea la cita con `payment_status` en un valor tipo `waived` o `not_required` y sin `payment_proof`. |
| **Doctor – Citas / Historial de pagos** | Ve citas pendientes y puede aprobar/rechazar pago (comprobante). | Para citas **sin pago**: no mostrar comprobante ni acciones "Aprobar pago" / "Rechazar pago". El doctor solo **asigna/aprueba el turno** (cambiar estado a aceptada, completada, etc.). Flujo: "Asignar turno" sin paso de verificación de pago. |
| **Backend** | `Patient\AppointmentController@store` exige `payment_proof`. | Si el sistema está en "modo sin pago" (config/flag o plan $0): no validar `payment_proof`; crear `Appointment` con `payment_status = 'not_required'` o similar y sin archivo. El doctor aprueba solo el turno (estado de la cita). |

**Resumen flujo cita sin pago:**  
Paciente solicita turno (sin subir pago) → Doctor aprueba/asigna el turno → Cita queda asignada. La sección de pagos en doctor y paciente queda deshabilitada/comentada, no eliminada.

### 1.4 Dónde aplicar los cambios (referencia)

- **Admin:** `resources/js/Pages/Admin/Subscriptions.vue` – Ocultar monto/comprobante para planes $0; solo aprobar/rechazar.
- **Doctor:** `resources/js/Pages/Doctor/Membership.vue` – Si `plan.price === 0`, no mostrar pasos de pago; solo "Activar plan".
- **Doctor:** `resources/js/Pages/Doctor/Payments.vue` – No mostrar (o deshabilitar) aprobación de pago cuando las citas sean sin pago; solo gestión de estado del turno.
- **Paciente:** `resources/js/Pages/Patient/DoctorProfile.vue` (o vista de reserva) – Quitar obligatoriedad de comprobante; formulario solo turno.
- **Backend:** `App\Http\Controllers\Doctor\MembershipController.php`, `App\Http\Controllers\Patient\AppointmentController.php`, y si aplica `App\Http\Controllers\Admin\SubscriptionController.php`.

---

## 2. Sistema por día (sin horarios específicos)

### 2.1 Objetivo

- El sistema debe manejarse **por día**, no por hora.
- No se elige "de 9:00 a 9:30"; se elige **día** y, opcionalmente, **bloque**: Mañana, Tarde o Noche (o "Todo el día").
- En el panel del doctor y del usuario solo se ve y gestiona **turno** (posición del día), no hora exacta.

### 2.2 Bloques de turno (Mañana / Tarde / Noche)

- **Mañana:** ej. 06:00 – 12:00 (configurable desde admin o BD).
- **Tarde:** ej. 12:00 – 18:00 (configurable).
- **Noche:** ej. 18:00 – 22:00 (configurable).
- **Todo el día:** el doctor ofrece turnos en cualquiera de los bloques ese día.

El doctor puede elegir, por día:
- Solo mañana  
- Solo tarde  
- Solo noche  
- Mañana y tarde  
- Cualquier combinación o "Todo el día".

Los rangos horarios (ej. qué hora es "mañana", "tarde", "noche") se definen:
- **Opción A:** En la base de datos, tabla de configuración manejada por **admin** (por centro o global).
- **Opción B:** Valores **globales** en `.env` o `config/` (ej. `turno_manana_inicio`, `turno_manana_fin`, etc.) para toda la plataforma.

**Recomendación:** Definir en BD desde admin (tabla `settings` o `turn_slots_config`) para no tocar código al cambiar horarios.

### 2.3 Rango de turnos y "Detener venta"

- El doctor (o el admin) define un **máximo de turnos por día** (por bloque o total del día), ej. "10 turnos por día" o "5 en la mañana, 5 en la tarde".
- Cuando se alcanza ese máximo, **no se pueden reservar más turnos** para ese día (o ese bloque).
- **Botón "Detener venta de turnos":** el doctor puede cerrar la venta de turnos para un día (o para "hoy") aunque no se haya llegado al máximo. Así se evita que sigan llegando solicitudes.

Campos sugeridos (en perfil del doctor o en tabla de disponibilidad):
- `max_turns_per_day` o por bloque (`max_turns_morning`, `max_turns_afternoon`, `max_turns_night`).
- `sales_stopped_at` (fecha/hora) o flag `turns_stopped_for_date` por día: cuando está activo, no se muestran más turnos disponibles para ese día.

### 2.4 Visualización del turno (contador)

- En un lugar **visible** en:
  - Panel del **doctor** (lista de citas del día, detalle de cita).
  - Panel del **paciente** (mi cita, confirmación).
- Mostrar algo como: **"Turno 3 de 10"** (posición 3 del día, máximo 10) o **"Turno 2 – Mañana"**.
- Esto puede ser un campo calculado (contando citas aceptadas/pendientes de ese día para ese doctor, en ese bloque si aplica) o un campo guardado en la cita (`turn_number`, `slot_label`).

### 2.5 Cambios técnicos sugeridos (resumen)

- **Modelo / BD:**  
  - Mantener o adaptar `time_slots`: en vez de `start_time`/`end_time` por cita, usar **fecha + bloque** (mañana/tarde/noche o "all_day").  
  - O nueva tabla `day_slots` o ampliar `time_slots` con `slot_type` = 'morning'|'afternoon'|'night'|'all_day' y sin hora exacta para el usuario.  
  - Configuración global o por doctor: rangos de mañana/tarde/noche y máximos de turnos.
- **Doctor:**  
  - Pantalla de "Disponibilidad" o "Turnos": por día, elegir bloques (mañana/tarde/noche) y tope de turnos; botón "Detener venta" por día.
- **Paciente:**  
  - Al reservar: elegir médico → día → bloque (mañana/tarde/noche) si el doctor lo tiene definido; sin elegir hora concreta.  
  - Ver en "Mis citas": día, bloque y "Turno X de Y".

---

## 3. Notificaciones de turno

### 3.1 Objetivo

- Cada vez que haya un **cambio de estado** del turno (nueva solicitud, turno aprobado, rechazado, completado, etc.), notificar a la persona relacionada (doctor o paciente).
- Canales: **correo** y **notificación web** (y en el futuro app móvil).
- Recordar al usuario que **active las notificaciones web** para recibirlas en el navegador.

### 3.2 Eventos que disparan notificación

- Paciente solicita turno → notificación al **doctor** (ya existe tipo "nueva cita").
- Doctor aprueba/rechaza turno o cambia estado (en consulta, completada, ausente) → notificación al **paciente**.
- Admin aprueba/rechaza suscripción del doctor → notificación al **doctor** (ya existe).
- Cualquier cambio de estado de la cita que afecte al paciente o al doctor debe enviar:
  - **Email** (Laravel Mail).
  - **Notificación web** (canal `database` de Laravel; el front ya las muestra en campana/centro de notificaciones).

### 3.3 Contenido sugerido

- **Título corto:** ej. "Turno aprobado", "Nueva solicitud de turno", "Tu cita fue rechazada".
- **Mensaje:** fecha del turno, médico (o paciente), número de turno si aplica (ej. "Turno 3 de 10"), y nuevo estado.
- **Enlace:** a la vista correspondiente (citas del doctor, mis citas del paciente, suscripciones del admin).

### 3.4 Recordatorio "Activar notificaciones web"

- En la web, en un lugar visible (ej. barra superior, perfil o primera vez que entra a "Citas" o "Notificaciones"):
  - Mensaje tipo: "Activa las notificaciones del navegador para recibir avisos de tus turnos."
  - Enlace o botón que dispare `Notification.requestPermission()` (o equivalente) y, si el usuario acepta, guardar preferencia (ej. en backend o en el front para no volver a mostrar el banner).
- Cuando esté implementado push (Firebase u otro), el mismo flujo puede pedir permiso para notificaciones push en móvil.

### 3.5 Implementación técnica (resumen)

- Revisar que todas las transiciones de estado de `Appointment` y de `Subscription` que afecten al otro rol disparen:
  - `$user->notify(new AppointmentStatusUpdated($appointment))` o equivalente.
  - Que la notificación use `via(): ['mail', 'database']`.
- Mantener una página o componente de **Notificaciones** (ya existe `Notifications.vue` / `NotificationController`) donde se listen las notificaciones en base de datos y, si se usa push, el service worker.
- Documentar en ayuda o onboarding: "Para no perderte ningún aviso, activa las notificaciones en tu navegador."

---

## 4. Checklist antes de desarrollar

- [ ] Confirmar que los planes $0 no deben mostrar ni guardar pago (suscripción y cita).
- [ ] Confirmar que la opción de pago se deja comentada/bloqueada, no eliminada.
- [ ] Definir si los bloques (mañana/tarde/noche) se configuran desde admin (BD) o desde config global.
- [ ] Definir si el "máximo de turnos" es por día total o por bloque (mañana/tarde/noche).
- [ ] Confirmar texto y ubicación del "Turno X de Y" (doctor y paciente).
- [ ] Confirmar que "Detener venta de turnos" es por día y que el doctor puede activarlo/desactivarlo.
- [ ] Confirmar lista de eventos que envían email + notificación web (citas y suscripciones).
- [ ] Confirmar que se mostrará el recordatorio de activar notificaciones web (y dónde).

---

## 5. Resumen ejecutivo

| Tema | Qué se pide |
|------|--------------|
| **Planes $0** | Sin formulario de pago en doctor ni admin; flujo de aprobación igual (doctor activa plan $0 → admin aprueba). Código de pago comentado/bloqueado, no borrado. |
| **Citas sin pago** | Paciente reserva sin comprobante; doctor solo aprueba/asigna el turno. Parte de pago deshabilitada/comentada. |
| **Turnos por día** | Sin hora exacta; día + bloque (mañana/tarde/noche o todo el día). Doctor define máximos y puede "detener venta". |
| **Contador** | Mostrar "Turno X de Y" (o "Turno 2 – Mañana") en panel doctor y paciente. |
| **Notificaciones** | En cada cambio de turno (o suscripción): email + notificación web al afectado; recordatorio de activar notificaciones en el navegador. |

Si este documento está bien para el equipo, se puede usar como referencia única para implementar y luego ajustar en detalle (nombres de campos, pantallas concretas) en las tareas de desarrollo.
