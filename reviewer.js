 document.addEventListener("DOMContentLoaded", function () {
        fetchSubjects();

        // Add event listener to the form submission
        document.getElementById("subject-form").addEventListener("submit", function (event) {
            event.preventDefault(); // Prevent the default form submission
            addSubject();
        });
    });

    function fetchSubjects() {
    fetch("reviewer.php?action=view")
        .then(response => response.json())
        .then(data => {
            let subjectList = document.getElementById("subjectList");
            subjectList.innerHTML = ""; // Clear existing content

            if (data.length === 0) {
                subjectList.innerHTML = "<p style='color:gray;'>No subjects added yet.</p>";
            } else {
                data.forEach(subject => {
                    let subjectButton = document.createElement("div");
                    subjectButton.className = "subject-button";

                    // Add subject name
                    let subjectName = document.createElement("span");
                    subjectName.className = "subject-name";
                    subjectName.textContent = subject.subject;

                    // Create menu container
                    let menuContainer = document.createElement("div");
                    menuContainer.className = "menu-container";

                    // Three-dots button
                    let menuButton = document.createElement("button");
                    menuButton.className = "menu-button";
                    menuButton.innerHTML = "&#x22EE;"; // Unicode for vertical dots
                    menuButton.onclick = (event) => {
                        event.stopPropagation(); // Prevent parent click event
                        toggleMenu(menuContainer);
                    };

                    // Dropdown menu
                    let menuDropdown = document.createElement("div");
                    menuDropdown.className = "menu-dropdown";
                    menuDropdown.innerHTML = `
                        <button class="update-btn">Update</button>
                        <button class="del">Delete</button>
                    `;

                    menuContainer.appendChild(menuButton);
                    menuContainer.appendChild(menuDropdown);

                    subjectButton.appendChild(subjectName);
                    subjectButton.appendChild(menuContainer);

                    // ✅ Prevent navigation when clicking Update/Delete
                    menuDropdown.querySelector(".update-btn").addEventListener("click", (event) => {
                        event.stopPropagation(); // Stop click from triggering parent event
                        openUpdateModal(subject.id, subject.subject);
                    });

                    menuDropdown.querySelector(".del").addEventListener("click", (event) => {
                        event.stopPropagation(); // Stop click from triggering parent event
                        deleteSubject(subject.id);
                    });

                    // 🔴 This should only trigger when clicking the subject itself
                    subjectButton.onclick = () => {
                        window.location.href = `subject_details.html?id=${subject.id}`;
                    };

                    subjectList.appendChild(subjectButton);
                });
            }
        })
        .catch(error => {
            console.error("Error fetching subjects:", error);
            document.getElementById("subjectList").innerHTML = "<p>Failed to load subjects.</p>";
        });
}


// Function to toggle menu visibility
function toggleMenu(menu) {
    document.querySelectorAll(".menu-dropdown").forEach(dropdown => {
        if (dropdown !== menu.querySelector(".menu-dropdown")) {
            dropdown.style.display = "none"; // Hide other menus
        }
    });

    let dropdown = menu.querySelector(".menu-dropdown");
    dropdown.style.display = dropdown.style.display === "block" ? "none" : "block";
}

// Close menu when clicking outside
document.addEventListener("click", () => {
    document.querySelectorAll(".menu-dropdown").forEach(dropdown => {
        dropdown.style.display = "none";
    });
});



    function addSubject() {
    let subject = document.getElementById("subjectSubject").value.trim();

    if (!subject) {
        alert("Subject title cannot be empty.");
        return;
    }

    // Fetch the logged-in user's ID
    fetch("get_user.php")
        .then(response => response.json())
        .then(data => {
            if (!data.success) {
                alert("User not logged in. Please log in first.");
                return;
            }

            let user_id = data.userid; // Correctly fetch user ID

            // Send subject and user_id to the database
            fetch("reviewer.php?action=add", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: `subject=${encodeURIComponent(subject)}&user_id=${user_id}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert("Subject added successfully!");
                    closeModal();
                    fetchSubjects(); // Refresh the subject list
                } else {
                    alert("Error: " + data.error);
                }
            })
            .catch(error => {
                console.error("Error:", error);
                alert("An error occurred while adding the subject.");
            });
        })
        .catch(error => {
            console.error("Error fetching user info:", error);
            alert("Failed to retrieve user information. Please log in again.");
        });
}


// Replace this with your logic to get the logged-in user's ID
function getLoggedInUserId() {
    // Example: Fetch the user ID from a session or local storage
    return localStorage.getItem("userId"); // Adjust this based on your authentication system
}

    function openModal() {
        document.getElementById("addSubjectModal").style.display = "block";
    }

    function closeModal() {
        document.getElementById("addSubjectModal").style.display = "none";
    }


    function deleteSubject(id) {
        if (confirm("Are you sure you want to delete this subject?")) {
            fetch("reviewer.php?action=delete", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: `id=${id}`
            })
            .then(response => response.json())
            .then(data => {
                alert(data.success || data.error);
                fetchSubjects(); // Refresh the subject list
            });
        }
    }




// Function to open the update modal
function openUpdateModal(subjectId, subjectName) {
    document.getElementById("updateSubjectId").value = subjectId;
    document.getElementById("updateSubjectName").value = subjectName;
    document.getElementById("updateSubjectModal").style.display = "block";
}

// Function to close the update modal
function closeUpdateModal() {
    document.getElementById("updateSubjectModal").style.display = "none";
}

// Prevent the modal from closing immediately
document.getElementById("update-subject-form").addEventListener("submit", function (e) {
    e.preventDefault();

    let subjectId = document.getElementById("updateSubjectId").value;
    let subjectName = document.getElementById("updateSubjectName").value.trim();

    if (!subjectName) {
        alert("Subject name cannot be empty.");
        return;
    }

    // Send update request via AJAX
    fetch("reviewer.php?action=update", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `subject_id=${encodeURIComponent(subjectId)}&subject_name=${encodeURIComponent(subjectName)}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert("Subject updated successfully!");
            closeUpdateModal();
            fetchSubjects(); // Refresh subject list
        } else {
            alert("Update failed: " + data.error);
        }
    })
    .catch(error => console.error("Error updating subject:", error));
});
