# Resumen para el dueño de la app – Turno Médico

Breve resumen de los cambios acordados para enviar al dueño de la app y validar antes de desarrollar.

---

## 1. Planes gratis ($0) – Sin pago por ahora

- Los **planes con precio $0** no pedirán datos bancarios ni comprobante.
- El doctor solo elige el plan y lo activa; el administrador lo aprueba como hasta ahora.
- En las **citas**, la parte de pago queda **desactivada**: el paciente pide turno sin subir comprobante; el doctor solo aprueba o asigna el turno.
- El código de pagos se deja **bloqueado/comentado**, no borrado, por si más adelante se quiere volver a usar.

---

## 2. Turnos por día (sin horarios exactos)

- Ya **no se elige hora** (ej. 9:00). Se elige **día** y bloque: **Mañana**, **Tarde** o **Noche** (o “Todo el día”).
- El doctor define cuántos turnos ofrece por día (o por bloque) y puede **cerrar la venta** de turnos para un día cuando quiera (“Detener venta”).
- En pantalla se verá algo como **“Turno 3 de 10”** para que doctor y paciente sepan en qué posición va el turno.

---

## 3. Notificaciones

- Cada vez que cambie algo en un turno (nueva solicitud, aprobado, rechazado, etc.), se enviará:
  - **Correo** al doctor o al paciente según corresponda.
  - **Notificación en la web** (campana / centro de notificaciones).
- Se mostrará un aviso para que el usuario **active las notificaciones del navegador** y no se pierda los avisos.

---

## Resumen en una frase

**Planes $0 y citas sin pago; turnos por día (mañana/tarde/noche) con tope y “detener venta”; y notificaciones por correo y web en cada cambio de turno.**

Si esto está bien, se puede pasar a desarrollo con el documento técnico completo.
