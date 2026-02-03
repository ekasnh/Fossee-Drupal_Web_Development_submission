```markdown
# Event Registration – Custom Drupal 10 Module

## Overview

`event_registration` is a fully custom **Drupal 10** module that allows administrators to configure events and enables users to register for those events through a dynamic, AJAX-powered registration form.

The module stores registration data in custom database tables, prevents duplicate registrations, and sends confirmation emails using Drupal's Mail API. It is built strictly without any contrib modules and follows Drupal 10 best practices.

---

## Deployment Environment

This module has been **developed, deployed, and tested using GitHub Codespaces**.

### Why GitHub Codespaces
- Provides a consistent and reproducible development environment
- Supports PHP 8.1+, Composer, and MySQL
- Suitable for Drupal 10 development and evaluation
- Ideal for technical assessments and code reviews

The same setup can be replicated locally or on any Linux-based production server.

---

## Installation Steps

### 1. Clone the Repository

```bash
git clone <your-github-repo-url>
cd <repository-name>
```

### 2. Start Drupal in GitHub Codespaces
Open the repository in GitHub Codespaces

Ensure the following are available:
- PHP 8.1+
- MySQL
- Composer

### 3. Install Dependencies
```bash
composer install
```

### 4. Place the Module
Ensure the module is located at:
```
/web/modules/custom/event_registration
```

### 5. Enable the Module
```bash
drush en event_registration -y
```

---

## Database Tables

The module uses custom database tables, created via the module install hook and also provided as an SQL file.

### 1. Event Configuration Table (`event_config`)

| Column | Description |
|--------|-------------|
| id | Primary key |
| event_name | Name of the event |
| category | Event category |
| registration_start | Registration start date |
| registration_end | Registration end date |
| event_date | Actual event date |

### 2. Event Registration Table (`event_registration`)

| Column | Description |
|--------|-------------|
| id | Primary key |
| event_id | Foreign key to event_config |
| full_name | Participant name |
| email | Participant email |
| college_name | College name |
| department | Department |
| created | Submission timestamp |

### Duplicate Prevention
- Duplicate registrations are prevented using a unique combination of **Email + Event ID**
- Enforced at both application and database level

---

## Available URLs

### Admin Pages

| Purpose | URL |
|---------|-----|
| Event Configuration | `/admin/config/event-registration/events` |
| Module Settings | `/admin/config/event-registration/settings` |
| Admin Listing Page | `/admin/event-registrations` |

### Public Page

| Purpose | URL |
|---------|-----|
| Event Registration Form | `/event/register` |

The registration form is automatically available only between the configured registration start and end dates.

---

## Functional Flow

### Event Configuration (Admin)
Administrators can create events with the following details:
- Event Name
- Category
- Registration start and end date
- Event date

All event data is stored in the `event_config` table.

### Event Registration (User)
Users register for events by selecting:
- **Category → Event Date → Event Name** (AJAX-driven dropdowns)

The form validates:
- Email format
- Special characters in text fields
- Duplicate registration (Email + Event Date)

### Validation Logic
- Email validation using Drupal's email validator
- Text fields allow only alphabets and spaces
- Duplicate prevention enforced at:
  - Application level
  - Database level
- All validation messages are user-friendly

---

## Email Notification Logic

Emails are sent using the **Drupal Mail API**.

### Recipients
- **User** (always)
- **Admin** (optional and configurable)

### Email Content Includes
- Participant Name
- Event Name
- Event Date
- Event Category

### Admin Email Configuration
Admin email settings are managed via:
```
/admin/config/event-registration/settings
```
- Stored using the **Config API**
- No hard-coded values are used

---

## Admin Listing Page

The admin listing page is accessible only to users with a custom permission.

### Features
**Filters:**
- Event Date
- Event Name (AJAX-based)

**Displays:**
- Total participant count
- Tabular registration data

**Allows exporting all filtered results as CSV**

---

## Permissions

Custom permission used by the module:
```
access event registration admin
```
This permission controls access to admin listing and reporting pages.

---

## Technical Highlights

- Drupal 10.x only
- No contrib modules
- Custom database tables
- AJAX callbacks for dependent dropdowns
- PSR-4 compliant services
- **Dependency Injection** used throughout  
  (No `\Drupal::service()` in business logic)
- Clean separation of:
  - Forms
  - Services
  - Controllers

---

## Repository Structure

```
event_registration/
├── src/
│   ├── Form/
│   ├── Controller/
│   ├── Service/
│   └── Ajax/
├── sql/
│   └── event_registration.sql
├── event_registration.install
├── event_registration.routing.yml
├── event_registration.permissions.yml
├── event_registration.services.yml
├── README.md
```

---

## Notes

- The module has been tested end-to-end in **GitHub Codespaces**
- Suitable for:
  - Technical evaluations
  - Drupal interviews
  - Enterprise demonstrations
- Easily deployable to any Linux-based Drupal 10 hosting

---

## Author: 
www.ekanshagarwal.co.in
```
