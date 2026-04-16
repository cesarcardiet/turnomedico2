# Informe: Flujos y lógica – Turno Médico

## 1. Credenciales de prueba (Seeder)

Tras ejecutar:

```bash
php artisan db:seed --class=CredencialesCesarSeeder
```

quedan creados/actualizados:

| Rol      | Email                  | Contraseña  |
|----------|------------------------|-------------|
| Admin    | admin@turnomedico.com  | Admin123!   |
| Doctor   | cesar1@gmail.com      | 123123123   |
| Paciente | cesar2@gmail.com      | 123123123   |

- **Admin**: panel de administración, aprobación de médicos y de pagos de suscripciones.
- **Doctor**: perfil aprobado y suscripción activa; puede recibir citas y aprobar pagos de citas.
- **Paciente**: puede buscar médicos y reservar citas.

---

## 2. Flujo Administrador ↔ Doctor

### 2.1 Registro del doctor

1. El doctor se registra en **Registro** con rol **Doctor**.
2. Se crea el usuario y un **DoctorProfile** con:
   - `is_approved = false`
   - `is_active = true`
3. Se notifica a los **admins** (DoctorRegistered).
4. El doctor puede entrar al panel pero **no aparece en la búsqueda pública** hasta que un admin lo apruebe.

### 2.2 Aprobación del doctor (Admin)

1. Admin entra en **Panel → Gestionar Médicos**.
2. Ve la lista de médicos; los no aprobados pueden verse como “pendientes” (dashboard muestra `pending_doctors`).
3. Admin puede:
   - Ver perfil/currículum del médico.
   - **Aprobar**: botón/acción “Aprobar”.
4. Al **aprobar**:
   - Se actualiza el perfil: `is_approved = true`, `is_active = true`.
   - Se envía notificación al doctor (**DoctorApproved**).
   - A partir de ese momento el médico **aparece en la búsqueda** de pacientes (`Patient\SearchController` y API filtran por `is_approved = true`).
5. El perfil debe estar **completo** (about, dirección, teléfono, especialidad) para que el doctor no vea avisos de “perfil incompleto”; la aprobación no rellena datos, solo marca `is_approved`.

### 2.3 Suscripción del doctor y aprobación de pago (Admin)

1. El doctor entra en **Mi suscripción**, elige un plan y “paga” (referencia/comprobante según tu flujo).
2. Se crea una **Subscription** con `payment_status = 'pending'`.
3. Admin entra en **Panel → Suscripciones** y ve las suscripciones pendientes.
4. Admin puede:
   - **Aprobar**: `payment_status = 'approved'`, se calculan `starts_at` y `ends_at` según el plan; se notifica al doctor (**SubscriptionUpdate** “aprobada”).
   - **Rechazar**: `payment_status = 'rejected'`; se notifica al doctor (SubscriptionUpdate “rechazada”).
5. Mientras el doctor no tenga una suscripción **approved** y vigente (`ends_at > now()`), el middleware **CheckSubscription** puede limitar el acceso a rutas de doctor (según tu configuración).
6. Resumen: **Admin aprueba el pago de la suscripción → doctor tiene suscripción activa → acceso completo al panel de doctor.**

---

## 3. Flujo Doctor ↔ Paciente (Citas y pagos)

### 3.1 Búsqueda y reserva (Paciente)

1. Paciente entra en **Buscar Médico** (o equivalente).
2. Solo se listan perfiles con **is_approved = true** y activos.
3. Elige un médico, ve sus horarios disponibles (**TimeSlot** no reservados).
4. Al reservar:
   - Sube **comprobante de pago** (imagen).
   - Se crea **Appointment** con:
     - `status = 'pending'`
     - `payment_status = 'pending'`
     - `payment_proof` = ruta del archivo
   - El **TimeSlot** pasa a `is_booked = true`.
   - Se notifica al **doctor** (AppointmentBooked).

### 3.2 Gestión de la cita por el doctor

El doctor ve las citas en **Citas** (y en “Historial de pagos” / pagos pendientes según la vista).

**Opción A – Aprobar/rechazar el pago de la cita**

1. Doctor entra en **Historial de pagos** (o vista de pagos pendientes).
2. Ve citas con `payment_status = 'pending'` y comprobante subido.
3. **Aprobar pago**:
   - `payment_status = 'verified'`
   - `status = 'accepted'`
   - Se notifica al **paciente** (AppointmentStatusUpdated).
4. **Rechazar pago**:
   - `payment_status = 'rejected'`, `status = 'rejected'`
   - El **TimeSlot** vuelve a `is_booked = false`.
   - Se notifica al paciente.

**Opción B – Cambiar solo el estado de la cita**

1. Doctor en **Citas** puede cambiar el **estado** de la cita: aceptada, rechazada, completada, ausente, en consulta.
2. Si pone **rechazada**, el turno se libera (`is_booked = false`) y se notifica al paciente.
3. Si el pago ya fue aprobado antes, la cita ya está aceptada; el doctor puede marcar después “completada”, “en consulta”, etc.

### 3.3 Notificaciones Doctor ↔ Paciente

- **Paciente → Doctor**: nueva cita reservada (AppointmentBooked).
- **Doctor → Paciente**: cambio de estado de cita o de estado de pago (AppointmentStatusUpdated: aceptada, rechazada, pago aprobado/rechazado, etc.).

El correo y/o la notificación en la app muestran el estado actual de la cita y del pago.

---

## 4. Resumen de lógica importante

| Qué | Dónde | Comportamiento |
|-----|--------|----------------|
| Perfil visible en búsqueda | Patient\SearchController, API | Solo `DoctorProfile.is_approved = true`. |
| Aprobación de médico | Admin\DoctorController@approve | Pone `is_approved = true`, `is_active = true` y notifica al doctor. |
| Aprobación de pago de suscripción | Admin\SubscriptionController@approve | Pone `payment_status = approved`, calcula fechas y notifica al doctor. |
| Acceso panel doctor | CheckSubscription | Requiere suscripción con `payment_status = approved` y `ends_at > now()`. |
| Creación de cita | Patient\AppointmentController@store | `status = pending`, `payment_status = pending`, turno se marca reservado, notifica al doctor. |
| Doctor aprueba pago de cita | Doctor\PaymentController@approve | `payment_status = verified`, `status = accepted`, notifica al paciente. |
| Doctor rechaza pago de cita | Doctor\PaymentController@reject | `payment_status = rejected`, `status = rejected`, libera turno, notifica al paciente. |
| Doctor cambia estado cita | Doctor\AppointmentController@updateStatus | Aceptada, rechazada, completada, etc.; si rechaza, libera turno y notifica. |

---

## 5. Cómo usar las credenciales y probar flujos

1. **Ejecutar el seeder** (en local o servidor):
   ```bash
   php artisan db:seed --class=CredencialesCesarSeeder
   ```
2. **Admin**: iniciar sesión con `admin@turnomedico.com` / `Admin123!` y probar aprobación de médicos y de suscripciones.
3. **Doctor**: iniciar sesión con `cesar1@gmail.com` / `123123123` y probar citas y aprobación de pagos de citas.
4. **Paciente**: iniciar sesión con `cesar2@gmail.com` / `123123123` y probar búsqueda y reserva de cita con el doctor cesar1.

Con esto tienes los dos flujos (Admin–Doctor y Doctor–Paciente) y la lógica de aprobaciones y notificaciones documentada en un solo informe.
