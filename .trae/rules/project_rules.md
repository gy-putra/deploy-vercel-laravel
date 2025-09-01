Umrah and Halal Tourism Management System Project Rules
This project, titled "Umrah and Halal Tourism Management System," will be developed using Laravel 11 and Filament. The primary goal is to create a robust, secure, and easy-to-use system for managing Umrah and halal tourism packages. The AI ​​agent must always prioritize code consistency, efficiency, and maintainability.

Code Style Rules
To ensure clean and structured code, all code must use 4 spaces for indentation, not tabs. Naming conventions follow established standards: camelCase for variables, properties, and functions (e.g., namaPaket, hitungHarga), PascalCase for classes and interfaces (e.g., PackageController), and snake_case for database column names and Blade files (e.g., package_name, booking_details.blade.php). Each complex class, function, and method should be accompanied by DocBlocks describing its purpose, parameters, and return value. Methods should be kept as concise as possible, ideally no longer than 25 lines, by breaking complex logic into smaller methods.

Language and Framework Rules
The primary framework used is Laravel 11. The AI ​​agent must take full advantage of Laravel features, such as Eloquent ORM, Blade, migrations, and routes. The admin panel will be developed entirely using Filament PHP; no custom Blade views will be used in the backend unless absolutely necessary. The frontend (public website) will use Blade with standard HTML, CSS, and JavaScript. For styling, Tailwind CSS should be used because it is well integrated with Laravel and Filament. The chosen database is MySQL, and all schema changes must be made through Laravel migrations.

Architecture and Structure Rules
The project structure must follow Laravel best practices. Each database table must have a corresponding Model (e.g., Package, Traveler, Booking). Complex business logic should be encapsulated in a Service or Action class to keep the controller streamlined. Policies must be implemented to manage authorization and user access control. For Filament, any model requiring a CRUD interface should be created as a Filament Resource. Filament Pages can be used for dashboards or custom reports.

API and Security Rules
It's important not to embed API keys or sensitive credentials directly in your code. All API keys and credentials should be stored in the Laravel .env file. All calls to external APIs, such as payment gateways or messaging services, should be wrapped in a dedicated service class. This will simplify management and future service provider replacement. Furthermore, user input validation should always be performed to prevent attacks like SQL injection and XSS. Using Laravel's Form Requests for validation is highly recommended.
