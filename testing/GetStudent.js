async function getStudents() {
  const response = await fetch('http://localhost/wordpress/wp-json/ica-lms/v1/students', {
    method: 'GET',
    credentials: 'include', // Include cookies for authentication
    headers: {
      'Authorization': 'Basic ' + btoa('ICAL-2026-00016:8787945687')
    }
  });
  
  const data = await response.json();
  console.log(data);
}

getStudents();