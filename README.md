# Intelline - Ticket Booking System

## Overview

The Intelline Ticket Booking System is a web application that allows users to book tickets for site-seeing events. Each booked ticket generates a unique ticket ID, which is associated with a PDF containing the ticket details and a QR code. Users can view their tickets by entering their ticket ID.

## Features

- User authentication and profile management.
- Ticket booking with automatic PDF generation.
- QR code generation for each ticket.
- Ticket retrieval using the unique ticket ID.
- Responsive design using Bootstrap.
- Secure data handling with parameterized queries.

## Technologies Used

- PHP
- MySQL
- HTML, CSS, JavaScript
- TCPDF for PDF generation
- PHP QR Code library for QR code generation
- Bootstrap for responsive design

## Prerequisites

- XAMPP or any other PHP and MySQL server
- Composer (for dependency management)

## Installation

1. **Clone the Repository**

   ```bash
   git clone https://github.com/your-username/intelline-ticket-booking.git
   cd intelline-ticket-booking

2. **Add Necessary Dependencies**

   Go to TCPDF github page `https://github.com/tecnickcom/TCPDF` and dowload the zip file and extract the file and import them into the project files
   and phpqrcode dependency too
   
3. **Setup Database**

   Open PhpMyAdmin using Xampp Control panel by starting Mysql and Apache servers and import the databases which are in `database/`.

