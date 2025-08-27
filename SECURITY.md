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

## Versiones soportadas
El repositorio se mantiene sobre la rama `main`.  
Cualquier vulnerabilidad debe reportarse respecto a esta rama.
