<?php
// Clear OPcache from web context
// Delete this file after use!

if (function_exists('opcache_reset')) {
    opcache_reset();
    echo "OPcache cleared successfully!";
} else {
    echo "OPcache not available";
}
