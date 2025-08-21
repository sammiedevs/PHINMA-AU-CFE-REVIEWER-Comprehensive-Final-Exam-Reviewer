// Get the notification modal and button elements
const notifModal = document.getElementById("notif-modal");
const notificationBtn = document.getElementById("notification-btn");
const closeBtn = document.querySelector("#notif-modal .close-button");

// Open the notification modal when the notification button is clicked
notificationBtn.addEventListener("click", (e) => {
    e.preventDefault(); // Prevent the default link behavior
    notifModal.style.display = "flex"; // Use flex to center the modal
});

// Close the notification modal when the close button is clicked
closeBtn.addEventListener("click", () => {
    notifModal.style.display = "none";
});

// Close the notification modal when clicking outside of it
window.addEventListener("click", (e) => {
    if (e.target === notifModal) {
        notifModal.style.display = "none";
    }
});

// Example: Update notification count and lists dynamically
const notificationCount = document.querySelector(".notification .num");
const studyRemindersList = document.getElementById("study-reminders");
const examUpdatesList = document.getElementById("exam-updates");

// Example data
const studyReminders = [
    "Complete Chapter 5 of Cost Accounting by tomorrow.",
    "Review Managerial Economics notes before the quiz.",
    "Practice Financial Markets problems for 1 hour.",
];

const examUpdates = [
    "Mock exam for COSMAN 1 scheduled on October 25th.",
    "Final exam for FINMAN 1 rescheduled to November 5th.",
    "Auditing Principles exam results will be released on October 30th.",
];

// Update the notification count
notificationCount.textContent = studyReminders.length + examUpdates.length;

// Populate the Study Reminders list
studyReminders.forEach((reminder) => {
    const li = document.createElement("li");
    li.textContent = reminder;
    studyRemindersList.appendChild(li);
});

// Populate the Exam Updates list
examUpdates.forEach((update) => {
    const li = document.createElement("li");
    li.textContent = update;
    examUpdatesList.appendChild(li);
});