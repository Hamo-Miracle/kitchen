<?php
/*
Template Name: User Schedule Page
*/
get_header();
?>

<!-- FullCalendar CSS and JS -->
<link href='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css' rel='stylesheet' />
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js'></script>

<style>
body {
  background: #d3e6e2 !important;
}

.user-schedule-container {
  max-width: 1200px;
  margin: 40px auto;
  padding: 20px;
  font-family: 'Segoe UI', Arial, sans-serif;
}

.top-section {
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  gap: 16px;
  margin-bottom: 30px;
  padding: 20px;
  background: #fff;
  border-radius: 8px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.04);
}

.user-section {
  width: 100%;
}

.user-dropdown {
  width: 100%;
  box-sizing: border-box;
}

.action-row {
  display: flex;
  align-items: center;
  gap: 24px;
  margin-top: 8px;
}

.user-label {
  font-weight: 600;
  color: #333;
  font-size: 14px;
}

.user-dropdown {
  padding: 10px 15px;
  border: 1px solid #ddd;
  border-radius: 6px;
  background: white;
  font-size: 14px;
  min-width: 200px;
}

.rating-button {
  background: #28a745;
  color: white;
  border: none;
  padding: 12px 24px;
  border-radius: 6px;
  font-weight: 600;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 8px;
  transition: background 0.3s;
  text-decoration: none;
}

.rating-button:hover {
  background: #218838;
}

.rating-section {
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  gap: 5px;
}

.rating-label {
  font-size: 12px;
  color: #666;
  font-weight: 500;
}

.rating-value {
  font-size: 24px;
  font-weight: bold;
  color: #333;
}

.calendar-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    padding: 15px 0;
    border-bottom: 1px solid #eee;
}

.calendar-title {
    font-size: 24px;
    font-weight: 600;
    color: #333;
}

.calendar-controls {
    display: flex;
    align-items: center;
    gap: 15px;
}

.calendar-nav {
    display: flex;
    align-items: center;
    gap: 10px;
}

.nav-button {
    background: none;
    border: 1px solid #ddd;
    padding: 8px 12px;
    border-radius: 4px;
    cursor: pointer;
    transition: background 0.3s;
}

.nav-button:hover {
    background: #f8f9fa;
}

.view-dropdown {
    padding: 8px 12px;
    border: 1px solid #ddd;
    border-radius: 4px;
    background: white;
}

.calendar-grid {
    border: 1px solid #ddd;
    border-radius: 8px;
    overflow: hidden;
}

.calendar-weekdays {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    background: #f8f9fa;
    border-bottom: 1px solid #ddd;
}

.weekday {
    padding: 15px;
    text-align: center;
    font-weight: 600;
    color: #666;
    font-size: 14px;
}

.calendar-days {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
}

.calendar-day {
    min-height: 100px;
    padding: 10px;
    border-right: 1px solid #eee;
    border-bottom: 1px solid #eee;
    position: relative;
}

.calendar-day:nth-child(7n) {
    border-right: none;
}

.calendar-day.other-month {
    background: #f8f9fa;
    color: #999;
}

.calendar-day.today {
    background: #e3f2fd;
}

.day-number {
    font-weight: 600;
    margin-bottom: 5px;
    font-size: 14px;
}

.event {
    background: #007bff;
    color: white;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 12px;
    margin-bottom: 2px;
    cursor: pointer;
    transition: background 0.3s;
}

.event:hover {
    background: #0056b3;
}

.weekend {
    background: #f8f9fa;
}

@media (max-width: 768px) {
    .top-section {
        flex-direction: column;
        align-items: flex-start;
        gap: 20px;
    }
}

/* FullCalendar customizations */
.calendar-grid {
  background: #fff;
  border-radius: 8px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.04);
  padding: 20px;
  margin-top: 20px;
}

/* Calendar header styling */
.fc .fc-toolbar {
  margin-bottom: 20px;
}

.fc .fc-toolbar-title {
  font-size: 24px;
  font-weight: 600;
  color: #333;
}

.fc .fc-button {
  background: #f8f9fa;
  border: 1px solid #ddd;
  color: #333;
  border-radius: 6px;
  padding: 8px 12px;
  font-size: 14px;
  margin: 0 2px;
  transition: background 0.3s;
}

.fc .fc-button:hover {
  background: #e9ecef;
}

.fc .fc-button-primary {
  background: #007bff;
  color: #fff;
  border: none;
}

.fc .fc-button-primary:hover {
  background: #0056b3;
}

/* Calendar grid styling */
.fc .fc-daygrid-day {
  min-height: 100px;
}

.fc .fc-daygrid-day-number {
  font-weight: 600;
  color: #333;
  padding: 8px;
}

.fc .fc-day-today {
  background: #e3f2fd !important;
}

.fc .fc-daygrid-day.fc-day-today .fc-daygrid-day-number {
  background: #007bff;
  color: #fff;
  border-radius: 50%;
  width: 30px;
  height: 30px;
  display: flex;
  align-items: center;
  justify-content: center;
}

/* Event styling */
.fc .fc-daygrid-event {
  background: #007bff;
  color: #fff;
  border-radius: 4px;
  font-size: 12px;
  padding: 4px 8px;
  margin: 2px 0;
  border: none;
}

.fc .fc-daygrid-event:hover {
  background: #0056b3;
}

/* Weekend styling */
.fc .fc-day-sun,
.fc .fc-day-sat {
  background: #f8f9fa;
}

/* Other month days */
.fc .fc-day-other {
  background: #f8f9fa;
  color: #999;
}

/* Add "67 schedules" text at bottom */
.calendar-grid::after {
  display: block;
  margin-top: 15px;
  font-size: 14px;
  color: #666;
  font-weight: 500;
}
</style>

<div class="user-schedule-container">
    <!-- Top Section -->
    <div class="top-section">
        <div class="user-section">
            <div class="user-label">USERS</div>
            <select id="user-dropdown" class="user-dropdown">
                <?php
                $users = get_users();
                foreach ($users as $user) {
                    echo '<option value="' . esc_attr($user->ID) . '">' . esc_html($user->user_login) . '</option>';
                }
                ?>
            </select>
        </div>
        <div class="action-row">
            <a href="https://forms.fillout.com/t/6MzEZuvNW5us" target="_blank" class="rating-button">
                <span>HOW DID IT GO?</span>
                <span>↗</span>
            </a>
            <div class="rating-section">
                <div class="rating-label">AVERAGE RATING</div>
                <div class="rating-value" id="avg-rating-value">
                    <?php
                    // Get the first user's rating as default
                    $users = get_users(['number' => 1]);
                    if (!empty($users)) {
                        $first_user = $users[0];
                        $rating = get_user_meta($first_user->ID, 'rating', true);
                        echo !empty($rating) && is_numeric($rating) ? number_format($rating, 2) : '0.00';
                    } else {
                        echo '0.00';
                    }
                    ?>
                </div>
                

            </div>
        </div>
    </div>



    <div class="calendar-grid">
        <div id="calendar"></div>
    </div>
</div>

<script>
let calendar; // Make calendar variable accessible globally

document.addEventListener('DOMContentLoaded', function() {
    const userDropdown = document.getElementById('user-dropdown');
    const avgRatingValue = document.getElementById('avg-rating-value');

    // Fetch average rating for selected user
    function fetchAverageRating(userId) {
        fetch('<?php echo admin_url('admin-ajax.php'); ?>?action=get_user_avg_rating&user_id=' + userId)
            .then(response => response.json())
            .then(data => {
                avgRatingValue.textContent = data.avg_rating || '0.00';
            })
            .catch(() => {
                avgRatingValue.textContent = '0.00'; // Fallback
            });
    }

    // On user change
    userDropdown.addEventListener('change', function() {
        fetchAverageRating(this.value);
        const selectedText = this.options[this.selectedIndex].text;
        loadAirtableEvents(selectedText);
    });

    // Initial load of rating
    fetchAverageRating(userDropdown.value);

    // Initialize FullCalendar
    var calendarEl = document.getElementById('calendar');
    calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        eventClick: function(info) {
            // Show details in a more user-friendly way
            const event = info.event;
            const props = event.extendedProps;
            
            // Create a custom popup or use alert
            alert(
                `Class at ${event.title}\n` +
                `Time: ${new Date(event.start).toLocaleString()}\n` +
                `Enrollment: ${props.enrollment}\n` +
                `Recipes: ${props.recipes}\n` +
                `Location: ${props.location}`
            );
        },
        eventDidMount: function(info) {
            // Add tooltips to events
            if (info.event.extendedProps.description) {
                const tooltip = info.event.extendedProps.description.replace(/\n/g, '<br>');
                info.el.setAttribute('title', tooltip);
            }
        },
        eventTimeFormat: {
            hour: 'numeric',
            minute: '2-digit',
            meridiem: 'short'
        }
    });
    calendar.render();

    // Function to load events from Airtable
    function loadAirtableEvents(userName) {
        // Show loading indicator (use a separate element for loading)
        const loadingIndicator = document.createElement('div');
        loadingIndicator.id = 'calendar-loading';
        loadingIndicator.style.cssText = 'text-align:center; padding:50px; position:absolute; background:rgba(255,255,255,0.8); width:100%; height:100%; z-index:10;';
        loadingIndicator.innerHTML = 'Loading calendar data...';
        
        // Add the loading indicator to the calendar container
        const calendarContainer = document.querySelector('.calendar-grid');
        calendarContainer.style.position = 'relative';
        calendarContainer.appendChild(loadingIndicator);
        
        fetch('<?php echo admin_url('admin-ajax.php'); ?>?action=get_airtable_classes&user_name=' + encodeURIComponent(userName))
            .then(response => response.json())
            .then(events => {
                // Remove the loading indicator
                const loadingElement = document.getElementById('calendar-loading');
                if (loadingElement) {
                    loadingElement.remove();
                }
                
                // Check if we got an error
                if (events.error) {
                    console.error('Error loading events:', events.error, events.details);
                    const errorDiv = document.createElement('div');
                    errorDiv.style.cssText = 'text-align:center; padding:50px; color:red;';
                    errorDiv.innerHTML = 'Error loading calendar data. Please try again.';
                    calendarContainer.appendChild(errorDiv);
                    return;
                }
                
                // Clear and re-render calendar
                calendar.removeAllEvents();
                calendar.addEventSource(events);
                
                // Update the count text
                const countElement = document.querySelector('.calendar-count');
                if (countElement) {
                    countElement.textContent = events.length + ' schedules';
                } else {
                    // If the count element doesn't exist, add it
                    const countDiv = document.createElement('div');
                    countDiv.className = 'calendar-count';
                    countDiv.style.cssText = 'margin-top:15px; font-size:14px; color:#666; font-weight:500;';
                    countDiv.textContent = events.length + ' schedules';
                    calendarContainer.appendChild(countDiv);
                }
            })
            .catch(error => {
                // Remove the loading indicator
                const loadingElement = document.getElementById('calendar-loading');
                if (loadingElement) {
                    loadingElement.remove();
                }
                
                console.error('Error fetching events:', error);
                const errorDiv = document.createElement('div');
                errorDiv.style.cssText = 'text-align:center; padding:50px; color:red;';
                errorDiv.innerHTML = 'Error loading calendar data. Please try again.';
                calendarContainer.appendChild(errorDiv);
            });
    }

    // Initial load of events
    const selectedText = userDropdown.options[userDropdown.selectedIndex].text;
    loadAirtableEvents(selectedText);
});
</script>

<?php get_footer(); ?>