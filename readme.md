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

### 1. Clone the Repository
```bash
git clone <your-github-repo-url>
cd <repo-name>
