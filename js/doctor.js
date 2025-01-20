// بيانات الطبيب
const doctor = {
    firstName: "John",
    lastName: "Doe",
    id: "12345",
    dob: "1985-07-15",
    email: "johndoe@example.com"

};

// بيانات المواعيد
const appointments = [
    { date: "23/2/2025", time: "11 AM", name: "Nora Saad", age: 15, gender: "Female", reason: "Headache", status: "Pending" },
    { date: "16/7/2025", time: "5 PM", name: "Majed Ahmad", age: 45, gender: "Male", reason: "Back pain", status: "Confirmed" }
];

// بيانات المرضى
const patients = [
    { name: "Leena Naser", age: 40, gender: "Female", medications: "Antibiotics" },
    { name: "Majed Saleh", age: 35, gender: "Male", medications: "N/A" }
];

// عرض بيانات الطبيب
document.getElementById("first-name").textContent = doctor.firstName;
document.getElementById("last-name").textContent = doctor.lastName;
document.getElementById("doctor-id").textContent = doctor.id;
document.getElementById("doctor-dob").textContent = doctor.dob;
document.getElementById("doctor-email").textContent = doctor.email;

function logout() {
    alert("You have been logged out.");
    // إعادة توجيه المستخدم إلى صفحة تسجيل الدخول
    window.location.href = "HomePage.html";
}
// عرض المواعيد
const appointmentTable = document.getElementById("appointment-table");
appointments.forEach(app => {
    const row = document.createElement("tr");
    row.innerHTML = `
        <td>${app.date}</td>
        <td>${app.time}</td>
        <td>${app.name}</td>
        <td>${app.age}</td>
        <td>${app.gender}</td>
        <td>${app.reason}</td>
        <td>${app.status === "Pending" ? '<button class="btn btn-success btn-sm">Confirm</button>' : app.status}</td>
    `;
    appointmentTable.appendChild(row);
});

// عرض المرضى
const patientTable = document.getElementById("patient-table");
patients.forEach(patient => {
    const row = document.createElement("tr");
    row.innerHTML = `
        <td>${patient.name}</td>
        <td>${patient.age}</td>
        <td>${patient.gender}</td>
        <td>${patient.medications}</td>
        <td><a href="Medication-Page" class="btn btn-primary btn-sm">Prescribe</a></td>
    `;
    patientTable.appendChild(row);
});
