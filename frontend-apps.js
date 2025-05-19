// Utility Functions
const BASE_URL = 'http://localhost/rideshare/backend/api';

const showNotification = (message, type = 'success') => {
    const notification = document.getElementById('notification');
    notification.textContent = message;
    notification.className = `alert alert-${type}`;
    notification.style.display = 'block';
    
    setTimeout(() => {
        notification.style.display = 'none';
    }, 3000);
};

const sendRequest = async (url, method, data) => {
    try {
        console.log('Sending request:', { url, method, data }); // Detailed logging
        const response = await fetch(url, {
            method: method,
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify(data)
        });

        const responseData = await response.json();
        console.log('Response:', responseData); // Log full response

        if (!response.ok) {
            throw new Error(responseData.error || 'Network response was not ok');
        }

        return responseData;
    } catch (error) {
        console.error('Full Error:', error);
        showNotification(error.message || 'An error occurred. Please try again.', 'danger');
        return null;
    }
};

// Registration Handler
document.getElementById('register-form')?.addEventListener('submit', async (e) => {
    e.preventDefault();
    const name = document.getElementById('register-name').value;
    const email = document.getElementById('register-email').value;
    const password = document.getElementById('register-password').value;
    const role = document.getElementById('register-role').value;

    if (!name || !email || !password || !role) {
        showNotification('Please fill in all fields', 'warning');
        return;
    }

    const registrationData = { name, email, password, role };
    const result = await sendRequest(`${BASE_URL}/register.php`, 'POST', registrationData);

    if (result && result.message === 'User registered successfully') {
        showNotification('Registration successful! Please log in.');
        // Optional: Switch to login section
    }
});

// Login Handler
document.getElementById('login-form')?.addEventListener('submit', async (e) => {
    e.preventDefault();
    const email = document.getElementById('login-email').value;
    const password = document.getElementById('login-password').value;

    if (!email || !password) {
        showNotification('Please enter email and password', 'warning');
        return;
    }

    const loginData = { email, password };
    const result = await sendRequest(`${BASE_URL}/login.php`, 'POST', loginData);

    if (result && result.message === 'Login successful') {
        showNotification('Login successful!');
        // Show ride booking section and hide login/register sections
        document.getElementById('login-section').style.display = 'none';
        document.getElementById('register-section').style.display = 'none';
        document.getElementById('ride-section').style.display = 'block';
    }
});

// Ride Booking Handler
document.getElementById('ride-form')?.addEventListener('submit', async (e) => {
    e.preventDefault();
    const pickupLocation = document.getElementById('pickup-location').value;
    const dropoffLocation = document.getElementById('dropoff-location').value;
    const fare = document.getElementById('ride-fare').value;

    if (!pickupLocation || !dropoffLocation || !fare) {
        showNotification('Please fill in all ride details', 'warning');
        return;
    }

    // In a real-world scenario, you'd get the passenger_id and driver_id from the session
    const rideData = {
        passenger_id: 1, // Placeholder
        driver_id: 2,    // Placeholder
        pickup_location: pickupLocation,
        dropoff_location: dropoffLocation,
        fare: parseFloat(fare)
    };

    const result = await sendRequest(`${BASE_URL}/createRide.php`, 'POST', rideData);

    if (result && result.message === 'Ride created successfully') {
        showNotification('Ride booked successfully!');
        // Optional: Clear form or provide additional feedback
    }
});
