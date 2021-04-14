<!DOCTYPE html>
<html>
<head>
	<title></title>
</head>
<body>
		<script type="text/javascript" src="js/jquery.js"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
      <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.9.0/moment.min.js"></script>
				<link href='https://cdn.jsdelivr.net/npm/fullcalendar-scheduler@5.3.0/main.min.css' rel='stylesheet' />
  				<script src='https://cdn.jsdelivr.net/npm/fullcalendar-scheduler@5.3.0/main.min.js'></script>
		<script type="text/javascript">
			document.addEventListener('DOMContentLoaded', function() {
  var calendarEl = document.getElementById('calendar');

  var calendar = new FullCalendar.Calendar(calendarEl, {
    headerToolbar: {
      left: 'prev,next today',
      center: 'title',
      right: 'dayGridMonth,timeGridWeek'
    },
    initialDate: '2020-10-12',
    businessHours: true, // display business hours
    editable: true,
    events: [
      {
        title: 'Business Lunch',
        start: '2020-10-03T13:00:00',
        constraint: 'businessHours'
      },
      {
        title: 'Meeting',
        start: '2020-10-13T11:00:00',
        constraint: 'availableForMeeting', // defined below
        color: '#257e4a'
      },
      {
        title: 'Conference',
        start: '2020-10-18 13:00:00',
        end: '2020-10-20 15:00'
      },
      {
        title: 'Party',
        start: '2020-10-29T20:00:00'
      },

      // areas where "Meeting" must be dropped
      {
        groupId: 'availableForMeeting',
        start: '2020-10-11T10:00:00',
        end: '2020-10-11T16:00:00',
        display: 'background'
      },
      {
        groupId: 'availableForMeeting',
        start: '2020-10-13T10:00:00',
        end: '2020-10-13T16:00:00',
        display: 'background'
      },

      // red areas where no events can be dropped
      {
        start: '2020-10-24',
        end: '2020-10-28',
        overlap: false,
        display: 'background',
        color: '#ff9f89'
      },
      {
        start: '2020-10-06',
        end: '2020-10-08',
        overlap: false,
        display: 'background',
        color: '#ff9f89'
      }
    ]
  });

  calendar.render();
});
</script>
<div id='currenttime'></div>
<div id='calendar'></div>
</body>
</html>