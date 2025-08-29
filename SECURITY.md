# Política de Seguridad

## Reporte de Vulnerabilidades
Si descubres una vulnerabilidad en este proyecto, por favor **no abras un issue público**.  
En su lugar, utiliza el canal de seguridad de GitHub:  
[Reportar vulnerabilidad](../../security/advisories/new)

Alternativamente, puedes contactar al responsable del repositorio de manera privada.

## Buenas prácticas aplicadas en este repo
- El archivo `.env` **no se encuentra en el repositorio**.  
- Se provee un `.env.example` con valores de referencia.  
- Dependencias sensibles (vendor, node_modules) están excluidas vía `.gitignore`.
- Se creó el entorno **`dev`** en GitHub con los Secrets necesarios para que los flujos de CI/CD funcionen sin exponer claves.
- Los valores son **ocultos** y solo accesibles por los workflows configurados.
- Ejemplos de Secrets configurados:  
  - `APP_KEY`, `APP_URL`  
  - `DB_HOST`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`  
  - `MAIL_*` (para envío de correos)  
  - Claves de servicios externos (cuando aplique)

## Versiones soportadas
El repositorio se mantiene sobre la rama `main`.  
Cualquier vulnerabilidad debe reportarse respecto a esta rama.

## Uso de Secrets

Este proyecto utiliza **GitHub Environments y Secrets** para proteger credenciales sensibles:

- **Nunca** almacenar contraseñas, tokens o credenciales en el repositorio ni en commits.
- Usar siempre los **Secrets de GitHub** para inyectar variables en los workflows.
- Limitar el acceso a Secrets por **Environment** y restringir qué ramas o flujos pueden usarlos.
- Rotar periódicamente contraseñas y claves de acceso.
- Documentar cambios en variables sensibles sin incluir su valor.
- Para servicios externos (ej. Cloudways), las credenciales se gestionan en la plataforma y **no deben guardarse en este repo**.

## Reporte de vulnerabilidades

Si detectas un problema de seguridad, por favor:
1. No abras un issue público con credenciales o datos sensibles.
2. Contacta al equipo de desarrollo internamente para la resolución.
