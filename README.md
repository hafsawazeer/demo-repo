# FitVerse - Nutritionist Management System

A comprehensive CRUD (Create, Read, Update, Delete) system for managing nutritionists as a supervisor in the FitVerse fitness platform.

## Features

### ✅ Complete CRUD Operations
- **Create**: Add new nutritionists manually with full details
- **Read**: View all nutritionists with search and filtering
- **Update**: Edit nutritionist details and status
- **Delete**: Remove nutritionists from the system

### 🎯 Key Functionality
- **Add Nutritionist**: Plus button in top-right corner opens modal form
- **Edit Details**: Click edit button to modify nutritionist information
- **Delete Nutritionist**: Remove nutritionists with confirmation dialog
- **Status Management**: Update nutritionist status (pending/active/inactive)
- **Search & Filter**: Find nutritionists by name, email, NIC, or specialization
- **Statistics Dashboard**: Real-time counts of total, pending, active, and inactive nutritionists

### 🔧 Technical Features
- Responsive design with modern UI
- Form validation and error handling
- File upload for NIC images
- Secure password handling
- Database transactions for data integrity
- Search functionality with real-time updates

## Installation & Setup

### Prerequisites
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web server (Apache/Nginx)
- Modern web browser

### Database Setup

1. **Create Database**:
   ```sql
   CREATE DATABASE fitverse_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```

2. **Import Database Structure**:
   - Run the SQL commands in `database_setup.sql`
   - Or import the file directly into your MySQL database

3. **Update Database Configuration**:
   - Edit `config.php` with your database credentials:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'fitverse_db');
   define('DB_USER', 'your_username');
   define('DB_PASS', 'your_password');
   ```

### File Structure Setup

Create the following directory structure:
```
FitVerse/
├── app/
│   ├── config/
│   │   └── config.php
│   ├── controllers/
│   │   └── SupervisorController.php
│   ├── models/
│   │   └── NutritionistModel.php
│   └── views/
│       └── supervisor/
│           ├── manageNutritionist.php
│           └── supervisor_manage.css
└── uploads/
    ├── nic_images/
    ├── certifications/
    └── profile_images/
```

### File Permissions

Set appropriate permissions for upload directories:
```bash
chmod 755 uploads/
chmod 755 uploads/nic_images/
chmod 755 uploads/certifications/
chmod 755 uploads/profile_images/
```

## Usage Guide

### Accessing the System
1. Navigate to `manageNutritionist.php` in your web browser
2. The dashboard shows statistics and a list of all nutritionists

### Adding a New Nutritionist
1. Click the **"+ Add Nutritionist"** button in the top-right corner
2. Fill in the required information:
   - Personal details (name, email, phone, gender, DOB, NIC)
   - Professional information (specialization, experience, certifications)
   - Account credentials (password)
   - Upload NIC image (optional)
3. Click **"Add Nutritionist"** to save

### Editing Nutritionist Details
1. Click the **"Edit"** button next to any nutritionist
2. Modify the information in the modal form
3. Click **"Update Nutritionist"** to save changes

### Viewing Nutritionist Details
1. Click **"View Details"** to see complete information
2. Update status directly from the details view
3. View uploaded NIC images

### Deleting a Nutritionist
1. Click the **"Delete"** button next to any nutritionist
2. Confirm the deletion in the dialog
3. The nutritionist and their user account will be permanently removed

### Search and Filter
- Use the search bar to find nutritionists by name, email, NIC, or specialization
- Sort by various criteria using the dropdown menu
- Results update automatically as you type

## Database Schema

### Main Tables

**user_table**: Stores basic user account information
- `user_id` (Primary Key)
- `name`, `email`, `phone`, `password`
- `role` (Client, Trainer, Nutritionist, Supervisor, Admin)
- `status` (Active, Inactive, Pending)

**nutritionist**: Stores professional nutritionist details
- `nutritionist_id` (Foreign Key to user_table)
- `specialization`, `experience_years`
- `certification`, `qualifications`
- `nic`, `nic_image_path`
- `status` (pending, active, inactive)

## Security Features

- Password hashing using PHP's `password_hash()`
- SQL injection prevention with prepared statements
- File upload validation and sanitization
- Input validation and sanitization
- CSRF protection ready (can be implemented)

## Error Handling

- Comprehensive error logging
- User-friendly error messages
- Database transaction rollback on failures
- File upload error handling

## Customization

### Styling
- Modify `supervisor_manage.css` for custom styling
- Uses CSS custom properties for easy theme changes
- Responsive design works on all devices

### Functionality
- Add new fields by updating the database schema and forms
- Extend validation rules in the controller
- Add new status types or user roles as needed

## Troubleshooting

### Common Issues

1. **Database Connection Failed**
   - Check database credentials in `config.php`
   - Ensure MySQL service is running
   - Verify database exists

2. **File Upload Issues**
   - Check upload directory permissions
   - Verify PHP upload settings (`upload_max_filesize`, `post_max_size`)
   - Ensure upload directories exist

3. **Form Validation Errors**
   - Check required field validation
   - Verify email format validation
   - Ensure password confirmation matches

4. **Search Not Working**
   - Check database table names match (case sensitivity)
   - Verify column names in search queries
   - Ensure proper indexing on searchable columns

## Browser Compatibility

- Chrome 80+
- Firefox 75+
- Safari 13+
- Edge 80+

## Performance Considerations

- Database queries are optimized with proper indexing
- File uploads are limited to 5MB
- Search queries use LIKE with wildcards efficiently
- Pagination can be added for large datasets

## Future Enhancements

- Bulk operations (import/export)
- Advanced filtering options
- Email notifications for status changes
- Audit trail for changes
- Role-based permissions
- API endpoints for mobile apps

## Support

For issues or questions:
1. Check the troubleshooting section
2. Review error logs in your web server
3. Verify database connections and permissions
4. Ensure all required files are in place

## License

This project is part of the FitVerse fitness platform. All rights reserved.