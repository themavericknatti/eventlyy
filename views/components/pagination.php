<?php

/**
 *  Pagination Component
 *
 * This file contains the HTML markup for the pagination component of the application.
 * It is included in the main layout and can be customized as needed.
 *
 * @package Evently
 * @subpackage Views/Components
 * @version 1.0.0
 */

?>

<div class="pagination" id="pagination">
    <!-- PHP: Link pages dynamically -->
    <a href="?page=1" class="page-button disabled" aria-disabled="true">Prev</a>
    <span class="page-button" aria-current="true">1</span>
    <a href="?page=2" class="page-button">2</a>
    <a href="?page=2" class="page-button">Next</a>
</div>