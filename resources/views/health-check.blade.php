<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lentlo Ads - Health Check</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f5f5f5;
            padding: 20px;
            max-width: 900px;
            margin: 0 auto;
        }
        h1 {
            color: #333;
            margin-bottom: 10px;
        }
        .subtitle {
            color: #666;
            margin-bottom: 30px;
        }
        .status-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .status-header {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 15px;
            flex-wrap: wrap;
        }
        .status-badge {
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 14px;
        }
        .status-healthy { background: #d4edda; color: #155724; }
        .status-issues { background: #f8d7da; color: #721c24; }
        .summary-counts {
            display: flex;
            gap: 15px;
            margin-left: auto;
        }
        .count-badge {
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 500;
        }
        .count-ok { background: #d4edda; color: #155724; }
        .count-warning { background: #fff3cd; color: #856404; }
        .count-error { background: #f8d7da; color: #721c24; }
        .section-title {
            font-size: 14px;
            font-weight: 600;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin: 20px 0 10px;
            padding-bottom: 8px;
            border-bottom: 2px solid #eee;
        }
        .check-item {
            display: flex;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid #eee;
        }
        .check-item:last-child { border-bottom: none; }
        .check-icon {
            width: 24px;
            height: 24px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 12px;
            font-size: 12px;
            flex-shrink: 0;
        }
        .icon-ok { background: #28a745; color: white; }
        .icon-warning { background: #ffc107; color: #333; }
        .icon-error { background: #dc3545; color: white; }
        .check-name {
            font-weight: 500;
            color: #333;
            min-width: 160px;
            flex-shrink: 0;
        }
        .check-message {
            color: #666;
            flex: 1;
            font-size: 14px;
        }
        .loading {
            text-align: center;
            padding: 40px;
            color: #666;
        }
        .refresh-btn {
            background: #667eea;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
        }
        .refresh-btn:hover { background: #5a6fd6; }
        .refresh-btn:disabled { background: #999; cursor: not-allowed; }
        .timestamp {
            color: #999;
            font-size: 12px;
            margin-top: 10px;
        }
        .back-link {
            display: inline-block;
            margin-bottom: 20px;
            color: #667eea;
            text-decoration: none;
        }
        .back-link:hover { text-decoration: underline; }
        @media (max-width: 600px) {
            .check-item { flex-wrap: wrap; }
            .check-name { min-width: 100%; margin-bottom: 5px; }
            .summary-counts { margin-left: 0; margin-top: 10px; width: 100%; }
        }
    </style>
</head>
<body>
    <a href="/admin" class="back-link">&larr; Back to Admin</a>
    <h1>Lentlo Ads - Health Check</h1>
    <p class="subtitle">Comprehensive system status and diagnostics</p>

    <div id="results" class="loading">Loading health checks...</div>

    <script>
        const sections = {
            'Database & Tables': ['database', 'users_table', 'listings_table', 'categories_table', 'messaging', 'reviews', 'reports'],
            'Storage & Files': ['storage', 'upload_test', 'storage_symlink', 'disk_space'],
            'Application': ['cache', 'logs', 'debug_mode', 'session', 'queue', 'mail', 'api_health', 'recent_listings'],
            'System': ['php_config', 'php_version', 'laravel_version', 'environment', 'timezone']
        };

        async function runHealthCheck() {
            const resultsDiv = document.getElementById('results');
            resultsDiv.innerHTML = '<div class="loading">Running health checks...</div>';

            try {
                const response = await fetch('/health-check/api');
                const data = await response.json();

                let html = `
                    <div class="status-card">
                        <div class="status-header">
                            <span class="status-badge ${data.summary.overall_status === 'healthy' ? 'status-healthy' : 'status-issues'}">
                                ${data.summary.overall_status === 'healthy' ? '&#10003; All Systems Healthy' : '&#9888; Issues Detected'}
                            </span>
                            <div class="summary-counts">
                                <span class="count-badge count-ok">${data.summary.passed} Passed</span>
                                ${data.summary.warnings > 0 ? `<span class="count-badge count-warning">${data.summary.warnings} Warnings</span>` : ''}
                                ${data.summary.errors > 0 ? `<span class="count-badge count-error">${data.summary.errors} Errors</span>` : ''}
                            </div>
                            <button class="refresh-btn" onclick="runHealthCheck()">Refresh</button>
                        </div>
                        <p class="timestamp">Checked: ${data.summary.checked_at} (${data.summary.total_checks} checks)</p>
                    </div>
                `;

                for (const [sectionName, keys] of Object.entries(sections)) {
                    html += `<div class="status-card"><div class="section-title">${sectionName}</div>`;

                    for (const key of keys) {
                        const check = data.checks[key];
                        if (!check) continue;

                        const iconClass = check.status === 'ok' ? 'icon-ok' :
                                         check.status === 'warning' ? 'icon-warning' : 'icon-error';
                        const icon = check.status === 'ok' ? '&#10003;' :
                                    check.status === 'warning' ? '!' : '&#10007;';

                        html += `
                            <div class="check-item">
                                <div class="check-icon ${iconClass}">${icon}</div>
                                <span class="check-name">${key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())}</span>
                                <span class="check-message">${check.message}</span>
                            </div>
                        `;
                    }

                    html += '</div>';
                }

                resultsDiv.innerHTML = html;

            } catch (error) {
                resultsDiv.innerHTML = `
                    <div class="status-card">
                        <div class="status-header">
                            <span class="status-badge status-issues">&#10007; Health Check Failed</span>
                            <button class="refresh-btn" onclick="runHealthCheck()">Retry</button>
                        </div>
                        <p style="color: #dc3545; margin-top: 10px;">Error: ${error.message}</p>
                    </div>
                `;
            }
        }

        runHealthCheck();
    </script>
</body>
</html>
