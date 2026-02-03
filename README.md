# Event Registration – Custom Drupal 10 Module

## Overview

`event_registration` is a fully custom **Drupal 10** module that enables administrators to configure events and allows users to register for them through a dynamic, AJAX-powered form.  
The module stores registrations in custom database tables, prevents duplicate entries, and sends confirmation emails using Drupal’s Mail API.

This module is built **without any contrib modules**, follows **Drupal 10 coding standards**, and uses **Dependency Injection** and **PSR-4 autoloading** throughout.

---

## Deployment Environment

This project has been **developed, deployed, and tested using GitHub Codespaces**.

### Why GitHub Codespaces
- Consistent development environment
- PHP 8.x and Composer support
- Easy Drupal 10 setup
- Suitable for evaluation and code review

The module runs inside a **Drupal 10 instance deployed in GitHub Codespaces** and can be reproduced locally or on any Linux-based server.

---

## Installation Steps

2. Start Drupal in GitHub Codespaces

Open the repository in GitHub Codespaces

Ensure PHP 8.1+, MySQL, and Composer are available

3. Install Dependencies
composer install

4. Place the Module

Ensure the module is located at:

/web/modules/custom/event_registration

5. Enable the Module
drush en event_registration -y

Database Tables

The module uses custom database tables created via the install hook and also provided as an SQL file.

1. Event Configuration Table (event_config)
Column	Description
id	Primary key
event_name	Name of the event
category	Event category
registration_start	Registration start date
registration_end	Registration end date
event_date	Actual event date
2. Event Registration Table (event_registration)
Column	Description
id	Primary key
event_id	Foreign key to event_config
full_name	Participant name
email	Participant email
college_name	College name
department	Department
created	Submission timestamp

Duplicate Prevention

Unique combination of Email + Event ID

Available URLs
Admin Pages
Purpose	URL
Event Configuration	/admin/config/event-registration/events
Module Settings	/admin/config/event-registration/settings
Admin Listing Page	/admin/event-registrations
Public Page
Purpose	URL
Event Registration Form	/event/register

The registration form is automatically available only between the configured start and end dates.

Functional Flow
Event Configuration (Admin)

Admin creates events with:

Name

Category

Registration window

Event date

Stored in event_config table

Event Registration (User)

User selects:

Category → Event Date → Event Name (AJAX driven)

Form validates:

Email format

Special characters in text fields

Duplicate registration (Email + Event Date)

Validation Logic

Email validation using Drupal’s email validator

Text fields allow only alphabets and spaces

Duplicate prevention enforced at application and database level

All validation messages are user-friendly

Email Notification Logic

Emails are sent using Drupal Mail API.

Recipients

User (always)

Admin (optional, configurable)

Email Content Includes

Participant Name

Event Name

Event Date

Event Category

Admin Email Configuration

Managed via:

/admin/config/event-registration/settings


Stored using Config API (no hard-coded values).

Admin Listing Page

Accessible only to users with a custom permission.

Features

Filter by:

Event Date

Event Name (AJAX-based)

Displays:

Total participant count

Tabular registration data

Export all filtered results as CSV

Permissions

Custom permission:

access event registration admin


Controls access to admin listing and reporting pages.

Technical Highlights

Drupal 10.x only

No contrib modules

Custom database tables

AJAX callbacks for dynamic dropdowns

PSR-4 compliant services

Dependency Injection (no \Drupal::service() in business logic)

Clean separation of Forms, Services, and Controllers

Repository Structure
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
