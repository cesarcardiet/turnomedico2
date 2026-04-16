<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Requerir comprobante de pago en citas
    |--------------------------------------------------------------------------
    | Si es false, el paciente puede reservar sin subir comprobante (turno sin pago).
    | Si es true, se exige comprobante como antes.
    */
    'require_appointment_payment' => env('TURNOMEDICO_REQUIRE_APPOINTMENT_PAYMENT', false),
];
