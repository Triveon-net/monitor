<?php
// Simple Router Loader

// Load route definitions safely
$authRoutes = [];
$publicRoutes = [];

if (file_exists(__DIR__ . '/routes/web.php')) {
	$authRoutes = include __DIR__ . '/routes/web.php';
	if (!is_array($authRoutes)) $authRoutes = [];
}
if (file_exists(__DIR__ . '/routes/public.php')) {
	$publicRoutes = include __DIR__ . '/routes/public.php';
	if (!is_array($publicRoutes)) $publicRoutes = [];
}

$routes = array_merge($publicRoutes, $authRoutes);

// Get requested route
$requestUri = $_SERVER['REQUEST_URI'];
$requestPath = parse_url($requestUri, PHP_URL_PATH);

// Remove leading slash except for root
if ($requestPath !== '/') {
	$requestPath = ltrim($requestPath, '/');
}

// Route dispatch
$matched = false;
$fileToLoad = null;

// Check exact routes first
if (isset($routes[$requestPath])) {
	$fileToLoad = $routes[$requestPath];
	$matched = true;
} else {
	// Check for dynamic routes with parameters
	foreach ($routes as $routePattern => $routeTarget) {
		// Check if route has parameters (contains {})
		if (strpos($routePattern, '{') !== false) {
			// Convert route pattern to regex
			$pattern = str_replace('/', '\/', $routePattern);
			$pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '([^\/]+)', $pattern);
			$pattern = '/^' . $pattern . '$/';
			
			if (preg_match($pattern, $requestPath, $matches)) {
				// Extract parameter names from route pattern
				preg_match_all('/\{([a-zA-Z0-9_]+)\}/', $routePattern, $paramNames);
				
				// Set $_GET parameters
				foreach ($paramNames[1] as $index => $paramName) {
					$_GET[$paramName] = $matches[$index + 1];
				}
				
				// Parse target to get file and query string
				$targetParts = explode('?', $routeTarget);
				$fileToLoad = $targetParts[0];
				
				// If target has query parameters with placeholders, replace them
				if (isset($targetParts[1])) {
					parse_str($targetParts[1], $queryParams);
					foreach ($queryParams as $key => $value) {
						// Replace placeholders in query values
						foreach ($paramNames[1] as $index => $paramName) {
							$value = str_replace('{' . $paramName . '}', $matches[$index + 1], $value);
						}
						$_GET[$key] = $value;
					}
				}
				
				$matched = true;
				break;
			}
		}
	}
}

if ($matched && $fileToLoad) {
	$filePath = __DIR__ . '/' . $fileToLoad;
	if (file_exists($filePath)) {
		// For API routes, use include to execute them
		if (strpos($fileToLoad, 'api/') !== false) {
			include $filePath;
		} else {
			require_once $filePath;
		}
		exit;
	} else {
		http_response_code(404);
		echo "File not found: $fileToLoad";
		exit;
	}
} else {
	http_response_code(404);
	echo "Route not found: $requestPath";
	exit;
}
