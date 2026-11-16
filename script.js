// script.js - small things only

// 1. Ask "are you sure?" on any button/link that has a data-confirm message
document.querySelectorAll("[data-confirm]").forEach(function (item) {
    item.addEventListener("click", function (event) {
        if (!confirm(item.getAttribute("data-confirm"))) {
            event.preventDefault();   // stop the click / form submit
        }
    });
});

// 2. Don't let people pick a date in the past when booking
var dateBox = document.getElementById("session_date");
if (dateBox) {
    var today = new Date().toISOString().split("T")[0];
    dateBox.setAttribute("min", today);
}

// 3. Search box on the tutor list: hides the cards that don't match
var searchBox = document.getElementById("tutor_search");
if (searchBox) {
    searchBox.addEventListener("keyup", function () {
        var text = searchBox.value.toLowerCase();
        document.querySelectorAll(".tutor-card").forEach(function (card) {
            if (card.innerText.toLowerCase().indexOf(text) > -1) {
                card.style.display = "";
            } else {
                card.style.display = "none";
            }
        });
    });
}

// 4. Check the register form BEFORE it is sent (front-end validation).
//    The PHP in register.php checks again, because JavaScript can be turned off.
var confirmBox = document.querySelector("input[name='confirm']");
if (confirmBox) {
    var regForm = confirmBox.closest("form");

    regForm.addEventListener("submit", function (event) {
        var id       = regForm.querySelector("input[name='student_id']").value.trim();
        var name     = regForm.querySelector("input[name='full_name']").value.trim();
        var password = regForm.querySelector("input[name='password']").value;
        var confirmP = confirmBox.value;
        var problem  = "";

        if (!/^[A-Za-z0-9-]{3,20}$/.test(id)) {
            problem = "Student ID must be 3 to 20 letters, numbers or dashes.";
        } else if (name.length < 2) {
            problem = "Please type your full name.";
        } else if (password.length < 6) {
            problem = "Password must be at least 6 characters.";
        } else if (password !== confirmP) {
            problem = "The two passwords are not the same.";
        }

        // remove the old message (if any)
        var old = document.getElementById("js_error");
        if (old) old.remove();

        if (problem !== "") {
            event.preventDefault();   // stop the form from being sent
            var box = document.createElement("div");
            box.id = "js_error";
            box.className = "alert alert-danger";
            box.innerText = problem;
            regForm.parentNode.insertBefore(box, regForm);
        }
    });
}
