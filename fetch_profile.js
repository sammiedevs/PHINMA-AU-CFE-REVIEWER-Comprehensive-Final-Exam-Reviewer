 // Get the profile modal and button elements
    const profilePopup = document.getElementById("profile-popup");
    const profileBtn = document.getElementById("profile-btn");
    const profileCloseBtn = document.querySelector("#profile-popup .close-button");
    const usernameField = document.getElementById("username");
    const useremailField = document.getElementById("useremail");
	const userfullnameField = document.getElementById("userfullname");
	const userphoneField = document.getElementById("userphone");

    // Open the profile modal when the profile button is clicked
    profileBtn.addEventListener("click", async (e) => {
    console.log("Profile button clicked");
    e.preventDefault(); // Prevent the default link behavior

    try {
        const response = await fetch("fetch_profile.php");
        const data = await response.json();
        console.log("Fetched profile data:", data);

        if (data.success === "1") {
            usernameField.textContent = data.data.username;
            useremailField.textContent = data.data.useremail;
            userfullnameField.textContent = data.data.userfullname;
            userphoneField.textContent = data.data.userphone;
            profilePopup.classList.add("show"); // Show the modal
        } else {
            alert("Failed to fetch profile data: " + data.message);
        }
    } catch (error) {
        console.error("Error fetching profile data:", error);
        alert("An error occurred while fetching profile data.");
    }
});

profileCloseBtn.addEventListener("click", () => {
    console.log("Close button clicked");
    profilePopup.classList.remove("show"); // Hide the modal
});

window.addEventListener("click", (e) => {
    if (e.target === profilePopup) {
        console.log("Clicked outside the modal");
        profilePopup.classList.remove("show"); // Hide the modal
    }
});