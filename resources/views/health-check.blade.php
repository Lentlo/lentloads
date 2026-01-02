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
            max-width: 800px;
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
        }
        .status-badge {
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 14px;
        }
        .status-healthy { background: #d4edda; color: #155724; }
        .status-issues { background: #f8d7da; color: #721c24; }
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
        }
        .icon-ok { background: #28a745; color: white; }
        .icon-warning { background: #ffc107; color: #333; }
        .icon-error { background: #dc3545; color: white; }
        .check-name {
            font-weight: 500;
            color: #333;
            min-width: 150px;
        }
        .check-message {
            color: #666;
            flex: 1;
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
        .timestamp {
            color: #999;
            font-size: 12px;
            margin-top: 10px;
        }
        @media (max-width: 600px) {
            .check-item { flex-wrap: wrap; }
            .check-name { min-width: 100%; margin-bottom: 5px; }
        }
    </style>
</head>
<body>
    <h1>Lentlo Ads - Health Check</h1>
    <p class="subtitle">System status and diagnostics</p>

    <div id="results" class="loading">Loading health checks...</div>

    <script>
        async function runHealthCheck() {
            const resultsDiv = document.getElementById('results');

            try {
                const response = await fetch('/health-check/api');
                const data = await response.json();

                let html = `
                    <div class="status-card">
                        <div class="status-header">
                            <span class="status-badge ${data.summary.overall_status === 'healthy' ? 'status-healthy' : 'status-issues'}">
                                ${data.summary.overall_status === 'healthy' ? '✓ All Systems Healthy' : '⚠ Issues Detected'}
                            </span>
                            <button class="refresh-btn" onclick="runHealthCheck()">Refresh</button>
                        </div>
                        <p class="timestamp">Last checked: ${data.summary.checked_at}</p>
                    </div>
                    <div class="status-card">
                `;

                for (const [key, check] of Object.entries(data.checks)) {
                    const iconClass = check.status === 'ok' ? 'icon-ok' :
                                     check.status === 'warning' ? 'icon-warning' : 'icon-error';
                    const icon = check.status === 'ok' ? '✓' :
                                check.status === 'warning' ? '!' : '✗';

                    html += `
                        <div class="check-item">
                            <div class="check-icon ${iconClass}">${icon}</div>
                            <span class="check-name">${key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())}</span>
                            <span class="check-message">${check.message}</span>
                        </div>
                    `;
                }

                html += '</div>';
                resultsDiv.innerHTML = html;

            } catch (error) {
                resultsDiv.innerHTML = `
                    <div class="status-card">
                        <div class="status-header">
                            <span class="status-badge status-issues">✗ Health Check Failed</span>
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
