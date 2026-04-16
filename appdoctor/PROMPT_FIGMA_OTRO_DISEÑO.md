# Prompt para otro IA de Cursor cuando cambies de archivo Figma

Cuando tengas **otro diseño** en tu misma cuenta de Figma y quieras que otro agente de Cursor lo use para la app, haz lo siguiente.

---

## 1. En Figma (tu cuenta)

- Abre el **nuevo archivo** o el diseño que quieras usar.
- Si es un archivo de la comunidad, haz **Save a copy** / **Duplicate** para tener una copia en tu cuenta.
- En **Share** → deja **“Anyone with the link can view”** (o que tu usuario tenga acceso).
- Copia la **URL** del archivo (ej: `https://www.figma.com/design/XXXXXXXX/...?node-id=0-1`).

El **token de Figma** que ya configuraste en Cursor (`mcp.json` con tu Personal Access Token) sirve para **todos los archivos de tu cuenta**. No hace falta configurar nada más; solo pasar el **nuevo enlace** al IA.

---

## 2. Qué decirle al otro IA (copia y pega esto)

Puedes copiar el bloque siguiente y **sustituir** la parte de la URL por tu nuevo diseño. Luego pégalo en el chat de Cursor cuando hables con otro agente.

```markdown
Tengo un diseño en Figma que quiero usar para la app Flutter de este proyecto.

**Enlace del archivo Figma (mi diseño):**  
[AQUÍ PEGA LA URL DE TU NUEVO ARCHIVO FIGMA]  
Ejemplo: https://www.figma.com/design/SiMNSwL080bzRAwK1i4t9Y/nombre-del-file?node-id=0-1

**Contexto del proyecto:**
- Proyecto: Turno Médico (backend Laravel en la raíz; app móvil en la carpeta `appdoctor`).
- La app Flutter está en `appdoctor/`: pantallas de inicio (lista de médicos), login/registro por rol (doctor/paciente), menú inferior (Inicio, Citas, Perfil).
- Las APIs y la URL base están en `appdoctor/lib/core/constants/api_constants.dart` (base: http://test.turnomedicord.com). No cambies la base ni los endpoints sin necesidad.
- La app está en **español** y usa una paleta en `appdoctor/lib/theme/app_theme.dart` (azul principal, fondos cálidos).

**Lo que quiero que hagas:**
1. Usar la integración de Figma en Cursor para **leer** el archivo del enlace de arriba (get_figma_data con el fileKey y nodeId de la URL).
2. A partir de ese diseño, **adaptar o crear** las pantallas necesarias en la app Flutter `appdoctor` (colores, textos, layout) manteniendo:
   - Español en toda la UI.
   - Conexión con las APIs existentes (auth, lista de doctores, etc.).
   - Estructura actual: `lib/theme/app_theme.dart`, `lib/screens/`, `lib/core/`.
3. Si el nuevo diseño tiene nuevas pantallas o flujos, implementarlos sin borrar la lógica de negocio ya conectada a la API.

Si no puedes acceder al Figma (ej. 403), dime y exporto pantallas a PNG/SVG en una carpeta del proyecto para que trabajes desde ahí.
```

Sustituye **`[AQUÍ PEGA LA URL DE TU NUEVO ARCHIVO FIGMA]`** por la URL real de tu nuevo diseño antes de enviar el mensaje.

---

## 3. Si cambias solo de “página” dentro del mismo archivo

Si sigues en el **mismo archivo** de Figma pero en otra **página** o **frame**:

- En la URL de Figma suele aparecer algo como `node-id=XXX-YYY`. Esa es la **node-id** del frame que estás viendo.
- En el prompt, además de la URL, puedes decir: “Usa el frame/node [nombre o node-id] para [nombre de la pantalla]”.

Ejemplo: *“Usa el frame ‘Nueva pantalla de pago’ (o node-id=1-123) para la pantalla de pago en la app.”*

---

## 4. Resumen rápido

| Qué quieres hacer | Dónde / qué hacer |
|-------------------|-------------------|
| Usar **otro archivo** Figma de tu cuenta | Comparte el **nuevo enlace** del archivo y usa el prompt de la sección 2 (con esa URL). |
| Mismo archivo, **otra página/frame** | Usa la misma URL y en el prompt indica el **nombre del frame** o el **node-id** que quieras usar. |
| El IA no puede leer Figma (403) | Exporta las pantallas a PNG/SVG en una carpeta del proyecto (ej. `appdoctor/design/`) y pide que trabaje desde ahí. |

Con esto puedes cambiar de archivo o de diseño en Figma y darle a otro IA de Cursor un prompt claro y listo para pegar.
