# Verificar que el servidor use el build nuevo

## 1. Dónde subir la carpeta build

En FileZilla (panel derecho = servidor) entra en:

```
/home/turnomed/test.turnomedicord.com/public/
```

Ahí debe existir la carpeta **build**. Abre esa carpeta en el servidor. En tu PC (panel izquierdo) entra en:

```
C:\Users\cesar\Desktop\turnomedico2\public\build\
```

Arrastra **todo** lo que hay dentro de tu `public\build` local:
- el archivo **manifest.json**
- la carpeta **assets** (con todos los .js y .css)

y suéltalo dentro de **public/build** del servidor, sobrescribiendo si pregunta.

Comprueba que en el servidor quede:
- `public/build/manifest.json`
- `public/build/assets/Subscriptions-DbbUa8-Y.js` (o un nombre parecido con Subscriptions-)
- `public/build/assets/app-2QHhSLNu.js` (o el que salga en tu último build)

## 2. En el servidor (SSH) limpiar caché de Laravel

```bash
cd /home/turnomed/test.turnomedicord.com
php artisan view:clear
php artisan cache:clear
php artisan config:clear
```

## 3. En el navegador

- Cierra todas las pestañas de test.turnomedicord.com.
- Abre una ventana de **incógnito/privada** (Ctrl+Shift+N en Chrome).
- Entra a: https://test.turnomedicord.com/admin/subscriptions

## 4. Cómo saber si está cargando el código nuevo

- Si el botón del comprobante dice **"Ver comprobante (aquí)"** y tiene fondo **indigo claro**, es el código nuevo. Al hacer clic debe abrirse el modal en la misma página.
- Si el botón sigue diciendo solo "Ver comprobante" o al hacer clic se abre otra pestaña, el navegador sigue usando el JS antiguo: repite el paso 1 (subir de nuevo **public/build** completo) y el paso 3 (incógnito).

## 5. Si sigue fallando: comprobar en el servidor qué build hay

Por SSH:

```bash
ls -la /home/turnomed/test.turnomedicord.com/public/build/
ls /home/turnomed/test.turnomedicord.com/public/build/assets/ | head -5
```

Deberías ver `manifest.json` y en `assets/` archivos como `Subscriptions-DbbUa8-Y.js`. Si los nombres son distintos a los de tu último `npm run build`, el servidor tiene un build viejo: vuelve a subir **toda** la carpeta `public/build` desde tu PC.
