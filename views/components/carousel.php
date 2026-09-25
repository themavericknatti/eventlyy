<?php

/**
 * Carousel Component
 *
 * This file contains the HTML markup for the carousel component of the application.
 * It is included in the main layout and can be customized as needed.
 *
 * @package Evently
 * @subpackage Views/Components
 * @version 1.0.0
 */

?>

<!-- Slide 1 -->
<a href="@url($link)" class="event-card-link">
    <article class="event-card">
        <div class="image">
            <div class="image-blur" style="background-image: url('@url($image)')"></div>
            <img src="@url($image)" alt="{{$event->event_name}}" class="image-full" />
        </div>
        <div class="meta">
            <h3>{{$event->event_name}}</h3>
            <p>{{$event->event_date}} • {{$event->event_location}}</p>
        </div>
    </article>
</a>