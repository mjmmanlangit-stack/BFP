let isDrawing = false;
let canvas = document.getElementById("signatureCanvas");
let ctx = canvas.getContext("2d");
let complianceStatus = "partially-compliant";

// Initialize canvas
function initCanvas() {
  canvas.width = canvas.offsetWidth;
  canvas.height = canvas.offsetHeight;
  ctx.strokeStyle = "#000";
  ctx.lineWidth = 2;
  ctx.lineCap = "round";
}

// Signature functionality
canvas.addEventListener("mousedown", startDrawing);
canvas.addEventListener("mousemove", draw);
canvas.addEventListener("mouseup", stopDrawing);
canvas.addEventListener("touchstart", handleTouch);
canvas.addEventListener("touchmove", handleTouch);
canvas.addEventListener("touchend", stopDrawing);

function startDrawing(e) {
  isDrawing = true;
  draw(e);
}

function draw(e) {
  if (!isDrawing) return;

  const rect = canvas.getBoundingClientRect();
  const x = e.clientX - rect.left;
  const y = e.clientY - rect.top;

  ctx.lineTo(x, y);
  ctx.stroke();
  ctx.beginPath();
  ctx.moveTo(x, y);
}

function stopDrawing() {
  if (isDrawing) {
    isDrawing = false;
    ctx.beginPath();
  }
}

function handleTouch(e) {
  e.preventDefault();
  const touch = e.touches[0];
  const mouseEvent = new MouseEvent(
    e.type === "touchstart"
      ? "mousedown"
      : e.type === "touchmove"
      ? "mousemove"
      : "mouseup",
    {
      clientX: touch.clientX,
      clientY: touch.clientY,
    }
  );
  canvas.dispatchEvent(mouseEvent);
}

function clearSignature() {
  ctx.clearRect(0, 0, canvas.width, canvas.height);
}

// Status selection
function selectStatus(status) {
  complianceStatus = status;
  document.querySelectorAll(".status-card").forEach((card) => {
    card.classList.remove("selected");
  });
  document.querySelector(`.status-card.${status}`).classList.add("selected");
}

// Violation management
function addViolation() {
  const container = document.getElementById("violations-container");
  const violationHtml = `
                <div class="violation-item">
                    <div class="violation-header d-flex align-items-center justify-content-between">
                        <div class="violation-title">
                            <i class="fas fa-exclamation-triangle text-danger me-2"></i>
                            New Violation
                        </div>
                        <button class="btn btn-outline-danger btn-sm" onclick="removeViolation(this)">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" rows="3" placeholder="Describe the violation..."></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Corrective Action</label>
                        <textarea class="form-control" rows="2" placeholder="Required corrective action..."></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Deadline for Compliance</label>
                        <input type="date" class="form-control">
                    </div>
                </div>
            `;
  container.insertAdjacentHTML("beforeend", violationHtml);
}

function removeViolation(button) {
  button.closest(".violation-item").remove();
}

// Photo upload
function handlePhotoUpload(input) {
  const files = input.files;
  const container = document.getElementById("uploaded-photos");

  for (let file of files) {
    if (file.type.startsWith("image/")) {
      const reader = new FileReader();
      reader.onload = function (e) {
        const photoHtml = `
                            <div class="col-md-3 mb-3">
                                <div class="position-relative">
                                    <img src="${e.target.result}" class="img-fluid rounded" style="height: 200px; object-fit: cover; width: 100%;">
                                    <button class="btn btn-danger btn-sm position-absolute top-0 end-0 m-1" onclick="this.closest('.col-md-3').remove()">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                        `;
        container.insertAdjacentHTML("beforeend", photoHtml);
      };
      reader.readAsDataURL(file);
    }
  }
}

// Recommendations
function addRecommendation() {
  const ol = document.querySelector(".form-section ol");
  const newLi = document.createElement("li");
  newLi.className = "mb-2";
  newLi.innerHTML = `
                <div class="d-flex justify-content-between align-items-center">
                    <input type="text" class="form-control me-2" placeholder="Enter new recommendation...">
                    <button class="btn btn-outline-danger btn-sm" onclick="this.closest('li').remove()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            `;
  ol.appendChild(newLi);
}

// Print functionality
function printReport() {
  window.print();
}

// Submit functionality
function submitReport() {
  // Gather form data
  const reportData = {
    establishment: document.getElementById("establishment").value,
    type: document.getElementById("establishmentType").value,
    date: document.getElementById("inspectionDate").value,
    time: document.getElementById("inspectionTime").value,
    riskLevel: document.getElementById("riskLevel").value,
    complianceStatus: complianceStatus,
    violations: [],
    notes: document.querySelector('textarea[placeholder*="inspector"]').value,
    signature: canvas.toDataURL(),
  };

  // Collect violations
  document.querySelectorAll(".violation-item").forEach((item) => {
    const violation = {
      description: item.querySelector("textarea").value,
      corrective: item.querySelectorAll("textarea")[1].value,
      deadline: item.querySelector('input[type="date"]').value,
    };
    reportData.violations.push(violation);
  });

  alert("Report submitted successfully!");
  console.log("Report Data:", reportData);
}

// Initialize on load
window.addEventListener("load", function () {
  initCanvas();
});

window.addEventListener("resize", function () {
  initCanvas();
});
