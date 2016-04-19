# Tinycar ![beta](https://cloud.githubusercontent.com/assets/12968858/13899877/55f5e02a-ee01-11e5-8ff5-6f4fe784d8fe.png)
An open source application for running small admin user interfaces.

### Summary
Tinycar is an application to run multiple administration user interfaces that are each defined with a single XML-file. This might be very useful to you, if you need a user-friendly tool to manage your custom content, but you don't have the time or know-how to develop one, let alone provide future support for it. Tinycar lets you focus on your solution rather than its administration.

### Features
- Application-specific datamodels and built-in SQLite-databases
- HTTP-accessable JSON API for external integrations
- 26 available UI components
- Support for multiple language versions
- Optional authentication mechanism
- Option to add application-specific services and webhooks

### Requirements
- PHP >= 5.3
- PHP extensions enabled: mbstring, PDO, pdo_sqlite

### Installation
1. Grant writing privileges to folder tinycar/storage and all of its subfolders
2. Complete the installation by pointing your web browser to tinycar/public/index.php  

### Development tools
- Gulp
- TypeScript

### Included integrations
- RequireJS, MIT-license, http://requirejs.org/
- jQuery, MIT-license, https://jquery.com/
- jQueryUI, MIT-license, http://jqueryui.com/
- Trumbowyg, MIT-license, https://alex-d.github.io/Trumbowyg/
- Simple Excel, MIT-license, https://github.com/faisalman/simple-excel-php

### License
Tinycar uses the MIT-license.
