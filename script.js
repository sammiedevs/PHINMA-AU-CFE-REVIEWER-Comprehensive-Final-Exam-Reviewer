const popupButtonLogin = document.getElementById('popup-button');
const popupContainerLogin = document.getElementById('login-popup');
const closeButtonLogin = document.querySelector('#login-popup .close-button');

popupButtonLogin.addEventListener('click', () => {
popupContainerLogin.style.display = 'block';
});
closeButtonLogin.addEventListener('click', () => {
popupContainerLogin.style.display = 'none';
});



function openSignUp() {
    document.getElementById('signup-popup').style.display = 'block';
    document.getElementById('login-popup').style.display = 'none';
}
function closeSignup(){
    document.getElementById('signup-popup').style.display='none';
}

function openLogin() {
    document.getElementById('login-popup').style.display = 'block';
    document.getElementById('signup-popup').style.display = 'none';
}





// Login
document.getElementById('login-form').onsubmit = function(event) {
    event.preventDefault(); 

    const useremail = document.getElementById('useremail').value;
    const userpassword = document.getElementById('userpassword').value;

    fetch('validate.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams({
            useremail: useremail,
            userpassword: userpassword
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success === "1") {
            window.location.href = 'student_dashboard.html'; 
        } else {
            alert(data.message); 
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Network Error: ' + error.message);
    });
};


//Register
document.getElementById('register-form').addEventListener('submit', function(event) {
    event.preventDefault(); 

    const formData = new FormData(this);

    fetch('register.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success === "1") {
            alert(data.message); // Show success message
        } else {
            alert(data.message); // Show error message
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred. Please try again.'); // Show error alert
    });
});



