<?php

/**
 * Event Component
 *
 * This file contains the HTML markup for the event component of the application.
 * It is included in the main layout and can be customized as needed.
 *
 * @package Evently
 * @subpackage Views/Components
 * @version 1.0.0
 */

?>

<!-- Event Card 1 -->
<article class="event-item">
    @isset($image)
    <div class="image">
        <div class="image-blur" style="background-image: url('@url($image)')"></div>
        <img src="@url($image)" alt="@isset($event->event_name){{ $event->event_name }}@endisset" class="image-full" />
    </div>
    @endisset
    <div class="details">
        <div>
            <h3>@isset($event->event_name){{ $event->event_name }}@endisset</h3>
            <p class="meta">@isset($event->event_date){{ $event->event_date }} • @endisset @isset($event->event_location){{ $event->event_location }}@endisset</p>
        </div>
        <a href="@url($link)" class="action">View Tickets</a>
    </div>
</article>