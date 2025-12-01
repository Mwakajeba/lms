# Laravel Log Viewer

A comprehensive log viewing and debugging tool for Laravel applications.

## Features

- **Real-time Log Viewing**: View all Laravel log files in a user-friendly interface
- **JSON Formatting**: Logs are parsed and displayed in a structured, readable format
- **Error Highlighting**: Error logs are highlighted in red for easy identification
- **Log Level Badges**: Color-coded badges for different log levels (ERROR, WARNING, INFO, DEBUG, etc.)
- **Expandable Context**: View detailed context and stack traces for each log entry
- **Clear Functionality**: Clear individual log files or all logs at once
- **Auto-refresh**: Page automatically refreshes every 30 seconds to show new logs
- **Responsive Design**: Works on desktop and mobile devices

## Access

Navigate to `/log` in your application to access the log viewer.

**Note**: Authentication is required to access the log viewer.

## Usage

### Viewing Logs

1. Go to `/log` in your browser
2. Use the tabs to switch between different log files (e.g., `laravel.log`, `scheduler.log`)
3. Each log entry shows:
   - Timestamp
   - Log level (with color-coded badge)
   - Environment
   - Message content
   - Context (if available)
   - Stack trace (for errors)

### Clearing Logs

- **Clear All Logs**: Click the "Clear All Logs" button to empty all log files (keeps the files but removes all content)
- **Refresh**: Click the "Refresh" button to reload the page and see updated logs

**Note**: The clear functionality empties the log files but keeps the files themselves. This allows new logs to be written to the same files.

### Log Levels and Colors

- **ERROR/CRITICAL/EMERGENCY/ALERT**: Red background, highlighted as errors
- **WARNING**: Yellow badge
- **INFO**: Blue badge
- **DEBUG**: Gray badge

## Security

- The log viewer is protected by authentication middleware
- Only authenticated users can access the logs
- Consider restricting access to admin users only in production

## File Structure

```
app/Http/Controllers/LaravelLogsController.php  # Controller handling log operations
resources/views/logs/laravel-logs.blade.php     # Main log viewer interface
routes/web.php                                  # Routes for log viewer
```

## Routes

- `GET /log` - View all logs
- `POST /log/clear` - Clear all log files (empty content but keep files)

## Customization

You can customize the log viewer by:

1. **Modifying the controller** (`LaravelLogsController.php`) to change log parsing logic
2. **Updating the view** (`laravel-logs.blade.php`) to change the interface
3. **Adjusting CSS** in the view's `@push('styles')` section for styling changes
4. **Modifying JavaScript** in the view's `@push('scripts')` section for behavior changes

## Troubleshooting

- If logs don't appear, check file permissions on the `storage/logs/` directory
- Ensure the log files exist and are readable by the web server
- Check Laravel's logging configuration in `config/logging.php`

## Production Considerations

- Consider implementing additional access controls for production environments
- Monitor log file sizes to prevent disk space issues
- Consider implementing log rotation for large applications
- The auto-refresh feature may impact performance with very large log files
