
---

# ⚙️ Sistema de Reservas – Backend (Producción)

```
# ⚙️ Sistema de Reservas – Backend

API REST robusta para la gestión de reservas con control de horarios, pagos y administración completa del sistema.

Este backend fue diseñado con reglas de negocio estrictas y una arquitectura escalable.

---

## 🧩 Funcionalidades principales

### 🔐 Autenticación
- Registro de usuarios
- Login
- Roles (usuario / propietario)

### 🏠 Gestión de salas
- Crear, editar y eliminar salas
- Modificar precio y descripción
- Control total por parte del propietario

### 📅 Reservas
- Creación de reservas por fecha, hora y duración
- Validación estricta de solapamientos
- Asociación de reservas a usuarios y salas
- Estados de reserva (pendiente / confirmada)

### 💳 Pagos
- Integración con pasarela de pago
- Confirmación automática de reservas tras el pago
- Manejo de errores de pago

---

## 🚫 Regla crítica del sistema

> **No se permiten reservas solapadas en la misma sala**

El backend valida:
- Fecha
- Hora de inicio
- Duración
- Sala seleccionada

Esta validación se realiza **exclusivamente en backend** para garantizar integridad del sistema.

---

## 🧱 Arquitectura

- Controllers  
  Manejo de requests y responses
- Services  
  Lógica de negocio y validaciones
- Models  
  Acceso a base de datos
- Helpers  
  Respuestas estandarizadas y manejo de errores

---

## 🛠️ Tecnologías utilizadas

- PHP
- Slim Framework
- MySQL / MariaDB
- PDO
- Arquitectura MVC
- API REST

---

## 🧠 Decisiones técnicas clave

- Separación clara de responsabilidades
- Validaciones de negocio centralizadas
- No se permite modificar reservas históricas
- Control total de integridad de datos
- Diseño preparado para escalar a múltiples salas

---

## ▶️ Instalación

```bash
composer install
php -S localhost:8000 -t public
