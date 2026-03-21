<?php
// sidebar-data.php - Only PHP functions, no HTML output
$currentPage = basename($_SERVER['PHP_SELF']);

function isActive($page)
{
    global $currentPage;
    
    // Handle array of pages
    if (is_array($page)) {
        return in_array($currentPage, $page) ? 'active' : '';
    }
    
    return ($currentPage == $page) ? 'active' : '';
}

// You can add more PHP functions here if needed
?>