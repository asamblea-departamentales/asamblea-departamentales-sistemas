# Changelog

## [v0.1.0] - 2025-08-29
### Added
- Primer corte estable del sistema (compila y arranca en local).
- Documentación inicial de decisiones (estrategia de ramas y convención de commits).
- Licencia MIT (público, sin costo).

### Changed
- **Requisito de plataforma:** actualización a **PHP 8.3 (x64)** para desarrollo local y CI.
  - Se actualizaron dependencias que requerían PHP 8.3.

### Fixed
- **CI de GitHub (Laravel CI):** corregido el workflow:
  - PHP actualizado a 8.3 en Actions.
  - Extensiones habilitadas: `pdo_sqlite`, `sqlite3`, `zip`.
  - Generación de `APP_KEY`, preparación de SQLite y migraciones previas a las pruebas.
  - Cache de Composer mejor configurada.

### Notes
- Este release sirve como punto de referencia estable para futuras integraciones.
