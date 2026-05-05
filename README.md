# 🚴 Aventura Bike - Sistema de Gestión de Taller y Alquiler de Bicicletas

Sistema integral desarrollado en **Laravel 10** para la gestión completa de un taller de reparación y servicio de alquiler de bicicletas.

---

## 📋 Descripción General

**Aventura Bike** es una aplicación web completa que permite gestionar:

1. **Taller de Reparación de Bicicletas** - Gestión de citas, presupuestos, reparaciones y seguimiento de componentes
2. **Servicio de Alquiler** - Reservas online y gestión de materiales de alquiler
3. **Gestión de Clientes** - Base de datos de usuarios y sus bicicletas
4. **Comunicaciones** - Notificaciones por WhatsApp y correo electrónico

---

## 🛠️ Tecnologías Utilizadas

### Backend
- **PHP 8.1+**
- **Laravel 10** - Framework principal
- **Laravel Sanctum** - Autenticación API
- **Laravel Breeze** - Scaffolding de autenticación

### Frontend
- **Blade Templates** - Motor de plantillas
- **Tailwind CSS 3** - Framework CSS
- **Alpine.js** - Interactividad JavaScript
- **Vite** - Bundler de assets

### Integraciones
- **Twilio SDK** - Envío de mensajes WhatsApp
- **DomPDF (barryvdh/laravel-dompdf)** - Generación de PDFs
- **Yajra DataTables** - Tablas dinámicas

### Base de Datos
- **MySQL/MariaDB** compatible

---

## 🏗️ Estructura del Proyecto

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── Alquiler/           # Controladores del módulo de alquiler
│   │   │   ├── AlquilerController.php
│   │   │   ├── AventuraBikeController.php
│   │   │   ├── MaterialController.php
│   │   │   └── UsuarioAlquilerController.php
│   │   ├── AppointmentController.php    # Gestión de citas
│   │   ├── BikeController.php           # Gestión de bicicletas
│   │   ├── ComponentController.php      # Componentes/piezas
│   │   ├── EnviarCorreosController.php  # Envío de correos
│   │   ├── MecanicoController.php       # Vista del mecánico
│   │   ├── PresupuestoController.php    # Presupuestos
│   │   ├── RecordatorioController.php   # Recordatorios automáticos
│   │   ├── RevisionController.php       # Revisiones de bicicletas
│   │   ├── UserController.php           # Gestión de usuarios
│   │   └── WhatsappController.php       # Integración WhatsApp
│   └── Middleware/
├── Mail/
│   ├── CitaCompletadaMail.php          # Email de cita completada
│   ├── PresupuestoMail.php             # Email con presupuesto
│   ├── RecordatorioRevisionMail.php    # Recordatorio de revisión
│   └── ReservaAlquilerMail.php         # Confirmación de reserva
└── Models/
    ├── Alquiler.php
    ├── AlquilerMaterial.php
    ├── Appointment.php
    ├── AppointmentComponent.php
    ├── AvisoEnviado.php
    ├── Bike.php
    ├── Component.php
    ├── Material.php
    ├── Presupuesto.php
    ├── PresupuestoItem.php
    ├── Revision.php
    ├── User.php
    ├── UsuarioAlquiler.php
    └── UsuarioAlquilerFoto.php
```

---

## 📦 Módulos del Sistema

### 1. 👥 Gestión de Usuarios

**Funcionalidades:**
- Registro y autenticación de usuarios
- Roles de usuario: `admin`, `user`, `premium`, `taller`
- Gestión de perfiles con nombre, email y teléfono
- Listado y búsqueda de usuarios con DataTables

**Rutas principales:**
- `/usuarios` - Listado de usuarios
- `/usuarios/create` - Crear nuevo usuario
- `/usuarios/{id}/edit` - Editar usuario
- `/usuarios/{id}/bicicletas` - Ver bicicletas de un usuario

---

### 2. 🚲 Gestión de Bicicletas

**Funcionalidades:**
- Registro de bicicletas asociadas a usuarios
- Información: nombre, marca, año, kilómetros, color
- Historial de revisiones por bicicleta
- Asociación con citas de taller

**Campos:**
| Campo | Descripción |
|-------|-------------|
| nombre | Nombre/modelo de la bicicleta |
| marca | Fabricante |
| anio_modelo | Año del modelo |
| kilometros | Kilómetros recorridos |
| color | Color de la bicicleta |

**Rutas:**
- `/bikes` - Listado de bicicletas
- `/bikes/create` - Nueva bicicleta
- `/bikes/{bike}/revisions` - Revisiones de una bicicleta

---

### 3. 🔧 Componentes y Revisiones

**Componentes:**
Catálogo de piezas/servicios del taller con:
- Nombre del componente
- Fecha de preaviso para recordatorios
- Precio y horas de taller estimadas
- Descripción detallada

**Revisiones:**
Sistema de seguimiento de mantenimiento:
- Fecha de revisión realizada
- Próxima revisión programada
- Asociación con componente específico
- Historial completo por bicicleta

**Recordatorios automáticos:**
- El sistema envía recordatorios vía WhatsApp/Email cuando se acerca una revisión
- Configurable días de preaviso por componente
- Registro de avisos enviados para evitar duplicados

---

### 4. 📅 Citas de Taller (Appointments)

**Estados de una cita:**
| Estado | Descripción |
|--------|-------------|
| `vacia` | Cita creada sin componentes asignados |
| `presupuesto` | Presupuesto creado, pendiente de aprobación |
| `pendiente` | Trabajo pendiente de iniciar |
| `en proceso` | Reparación en curso |
| `completada` | Trabajo finalizado |
| `denegado` | Presupuesto rechazado por el cliente |

**Prioridades:**
- 🔴 `premium` - Máxima prioridad (clientes premium + web)
- 🟠 `urgente` - Alta prioridad
- 🟢 `normal` - Prioridad estándar

**Funcionalidades:**
- Creación de citas con descripción del problema
- Asignación de mecánicos (múltiples)
- Seguimiento de componentes a reparar
- Gestión de tiempos y precios
- Calendario visual de citas
- Filtros por estado, prioridad y origen (web/tienda)
- Búsqueda por cliente, bicicleta o ID de programa

**Vistas:**
- `/citas` - Panel principal de citas
- `/citas/historico` - Historial de citas completadas
- `/calendario-citas` - Vista calendario
- `/calendario-asignado` - Citas asignadas a mecánicos
- `/mecanico` - Vista específica para mecánicos

---

### 5. 💰 Presupuestos

**Funcionalidades:**
- Generación de presupuestos detallados
- Listado de componentes con precios y horas
- Descuentos aplicables
- Generación de PDF profesional
- Envío por WhatsApp con enlace de confirmación
- Envío por correo electrónico con PDF adjunto
- Sistema de tokens para confirmación segura

**Flujo:**
1. Crear cita → Añadir componentes → Generar presupuesto
2. Enviar al cliente (WhatsApp/Email)
3. Cliente confirma/deniega vía enlace seguro
4. Si aprueba → pasa a cola de trabajo

**Rutas:**
- `/presupuestos` - Listado de presupuestos
- `/presupuestos/create/{user}` - Nuevo presupuesto para cliente
- `/presupuestos/{id}/factura` - Ver factura/presupuesto
- `/presupuestos/{id}/pdf` - Descargar PDF

---

### 6. 🔔 Sistema de Notificaciones

**WhatsApp (Twilio):**
- Envío de presupuestos con PDF adjunto
- Recordatorios de revisiones próximas
- Notificación de bicicleta lista para recoger
- Confirmación de reservas de alquiler

**Email:**
- Presupuestos con PDF adjunto
- Notificación de cita completada
- Recordatorios de revisiones
- Confirmación de reservas de alquiler

**Registro:**
Todos los avisos enviados se guardan en `avisos_enviados` para:
- Evitar envíos duplicados
- Auditoría de comunicaciones
- Estadísticas de notificaciones

---

### 7. 🚴‍♂️ Sistema de Alquiler

**Módulo completo de alquiler de bicicletas y material:**

#### Usuarios de Alquiler
Clientes específicos para el servicio de alquiler:
- Nombre, email, teléfono, DNI, dirección
- Historial de alquileres
- Almacenamiento seguro de fotos de DNI

#### Materiales Disponibles
Inventario de material para alquilar:
- **Tipos**: MTB 26, MTB 29, MTB 29 Doble, Eléctrica Paseo, Eléctrica Doble, Eléctrica Rígida, Carretera, Paseo, Niños, Cascos, Accesorios
- **Gestión de stock**: Stock total y disponible
- **Tallas disponibles**: XS, S, M, L, XL, XXL
- **Precio por día** y **precio de reserva/fianza**
- Estados: disponible, reservado, en uso, mantenimiento

#### Alquileres
| Campo | Descripción |
|-------|-------------|
| fecha_inicio | Inicio del alquiler |
| fecha_fin | Fin del alquiler |
| total_precio | Precio total calculado |
| reserva_precio | Fianza/depósito |
| descuento | Descuento aplicado |
| estado | Activo, Reservado, Finalizado |
| observaciones | Notas adicionales |
| web | Indica si viene de reserva online |
| incidencia | Detalle de problemas |
| fallo | Marca de fallo detectado |

#### Reservas Online (Web Pública)
Sistema público de reservas:
- Verificación de disponibilidad en tiempo real
- Selección de tipo de bicicleta y talla
- Múltiples bicicletas por reserva
- Subida obligatoria de foto del DNI
- Aceptación de condiciones de alquiler
- Envío automático de confirmación por email
- Protección honeypot anti-spam

**Rutas:**
- `/usuarios_alquiler` - Gestión de clientes de alquiler
- `/alquileres` - Alquileres activos y reservados
- `/alquileres/finalizado` - Historial de alquileres
- `/material` - Gestión de inventario
- `/calendario/alquiler` - Vista calendario de alquileres

---

### 8. 📊 Calendario y Planificación

**Vistas de calendario:**
- **Calendario de citas** - Visualización de citas programadas
- **Calendario de alquileres** - Reservas y alquileres activos
- **Calendario asignado** - Citas por mecánico

---

## 🔐 Sistema de Roles y Permisos

| Rol | Acceso |
|-----|--------|
| `admin` | Acceso completo a todas las funcionalidades |
| `taller` | Acceso a panel de mecánico y gestión de citas asignadas |
| `premium` | Cliente premium con prioridad |
| `user` | Cliente estándar |

**Redirección según rol:**
- Admin → `/presupuestos`
- User/Premium → `/miperfil`

---

## 🌐 Internacionalización

- Soporte para **Español** e **Inglés**
- Cambio de idioma mediante `/lang/{lang}`
- Almacenamiento de preferencia en sesión

---

## 📧 Plantillas de Email

- `emails/presupuesto.blade.php` - Email de presupuesto
- `emails/cita_completada.blade.php` - Bicicleta lista
- `emails/reserva2.blade.php` - Confirmación de reserva

---

## 📄 Generación de PDFs

- **Presupuestos** - PDF con detalle de trabajos y precios
- Almacenamiento en `storage/app/public/presupuestos/`
- Nombre de archivo: `presupuesto_{id}.pdf`

---

## ⚙️ Instalación

### Requisitos
- PHP >= 8.1
- Composer
- Node.js >= 16
- MySQL/MariaDB

### Pasos

```bash
# Clonar repositorio
git clone [url-repositorio]
cd aventura3

# Instalar dependencias PHP
composer install

# Instalar dependencias JavaScript
npm install

# Copiar archivo de configuración
cp .env.example .env

# Generar clave de aplicación
php artisan key:generate

# Configurar base de datos en .env
# DB_DATABASE=aventura
# DB_USERNAME=root
# DB_PASSWORD=

# Ejecutar migraciones
php artisan migrate

# Crear enlace simbólico para storage
php artisan storage:link

# Compilar assets
npm run build

# Iniciar servidor de desarrollo
php artisan serve
```

### Configuración de Twilio (WhatsApp)
```env
TWILIO_SID=your_account_sid
TWILIO_AUTH_TOKEN=your_auth_token
TWILIO_WHATSAPP_FROM=whatsapp:+14155238886
```

### Configuración de Email
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.example.com
MAIL_PORT=587
MAIL_USERNAME=your_email
MAIL_PASSWORD=your_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@aventurabike.com
MAIL_FROM_NAME="Aventura Bike"
```

---

## 📁 Almacenamiento de Archivos

```
storage/app/
├── private/
│   └── dnis/                    # Fotos de DNI (privado)
├── public/
│   └── presupuestos/           # PDFs de presupuestos
```

---

## 🧪 Testing

```bash
# Ejecutar tests
php artisan test

# Tests específicos
php artisan test --filter=NombreTest
```

---

## 📊 Diagrama de Relaciones (Modelos)

```
User
├── Bikes[] ─────────────┬── Revisions[]
│                        │       └── Component
│                        │
│                        └── Appointments[]
│                                └── Components[] (pivot: appointment_component)
│
└── Presupuestos[]
        └── PresupuestoItems[]
                └── Component

UsuarioAlquiler
└── Alquileres[]
        ├── Materiales[] (pivot: alquiler_material)
        └── UsuarioAlquilerFotos[]
```

---

## 🚀 Características Principales

- ✅ Gestión completa de taller de bicicletas
- ✅ Sistema de presupuestos con aprobación online
- ✅ Notificaciones WhatsApp y Email
- ✅ Recordatorios automáticos de revisiones
- ✅ Sistema de alquiler con reservas web
- ✅ Verificación de disponibilidad en tiempo real
- ✅ Panel específico para mecánicos
- ✅ Calendarios visuales
- ✅ Generación de PDFs profesionales
- ✅ Multi-idioma (ES/EN)
- ✅ Sistema de roles y permisos
- ✅ Diseño responsive con Tailwind CSS

---

## 📝 Licencia

Este proyecto es software propietario de **Aventura Bike**.

---

## 👨‍💻 Desarrollo

Desarrollado con ❤️ para la gestión eficiente de talleres de bicicletas.
