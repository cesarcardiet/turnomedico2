# Pasos en el servidor cuando la web se ve en blanco (cPanel)

## 1. Crear el enlace de storage

En el servidor (por SSH o Terminal de cPanel), dentro de la carpeta del proyecto:

```bash
cd /home/turnomed/test.turnomedicord.com
php artisan storage:link
```

Si dice que el enlace ya existe, está bien. Si antes dabas error con `ls public/storage`, después de este comando debería existir.

---

## 2. Subir la carpeta `public/build` desde tu PC

En el servidor **no tienes npm**, así que el frontend debe estar compilado en tu PC y subido.

- En tu **computadora** (donde tienes el proyecto), la carpeta `public/build` debe existir (con `manifest.json` y la carpeta `assets/`).
- **Sube toda la carpeta** `public/build` al servidor, en la misma ruta:  
  `test.turnomedicord.com/public/build/`  
  (o la ruta que sea tu `public` en el servidor).

Sin esta carpeta, Laravel no encuentra los JS/CSS y la página puede cargar en blanco o dar error.

---

## 3. Composer / vendor en el servidor

Si en el servidor `composer` no existe como comando:

**Opción A – Usar Composer desde cPanel**  
- En cPanel busca “PHP Composer” o “Composer” y úsalo para instalar dependencias en la carpeta del proyecto (donde está `composer.json`).

**Opción B – Subir `vendor` desde tu PC**  
- En tu PC, en la carpeta del proyecto, ejecuta:  
  `composer install --no-dev --optimize-autoloader`  
- Comprime la carpeta `vendor` (zip) y súbela al servidor.  
- Descomprímela en la raíz del proyecto (donde está `composer.json`), de modo que exista `vendor/autoload.php`, etc.

Si `vendor` no está o está incompleto, PHP puede fallar al cargar Laravel y la página quedará en blanco.

---

## 4. Ver el error real (log)

En el servidor:

```bash
cd /home/turnomed/test.turnomedicord.com
tail -50 storage/logs/laravel.log
```

Ahí verás el último error. Si hay “manifest not found”, “file not found” en `build/`, o “class not found”, sabrás si falta `public/build`, `vendor` o algo de configuración.

---

## 5. Resumen rápido

| Qué | Dónde / Cómo |
|-----|----------------|
| Enlace storage | En el servidor: `php artisan storage:link` |
| Frontend (JS/CSS) | Subir desde tu PC la carpeta `public/build` al servidor en `public/build` |
| Dependencias PHP | En el servidor: usar “PHP Composer” de cPanel **o** subir la carpeta `vendor` desde tu PC |
| Ver error | En el servidor: `tail -50 storage/logs/laravel.log` |

Después de subir `public/build` y tener `vendor` correcto (y si quieres `storage:link`), limpia caché y prueba de nuevo:

```bash
php artisan config:clear
php artisan view:clear
```

Luego recarga la web.
