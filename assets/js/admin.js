document.addEventListener("DOMContentLoaded", () => {
  // Declare lucide variable (assuming it's a global object provided by a library)
  let lucide
  if (window.lucide) {
    lucide = window.lucide
  }

  // Elements
  const adminSearchInput = document.getElementById("adminSearchInput")
  const adminSearchButton = document.getElementById("adminSearchButton")
  const adminFileList = document.getElementById("adminFileList")

  // Create toast container if it doesn't exist
  let toastContainer = document.querySelector(".toast-container")
  if (!toastContainer) {
    toastContainer = document.createElement("div")
    toastContainer.className = "toast-container"
    document.body.appendChild(toastContainer)
  }

  // Create preview modal
  const previewModal = document.createElement("div")
  previewModal.className = "preview-modal"
  previewModal.innerHTML = `
    <div class="preview-content">
      <div class="preview-header">
        <div class="preview-title"></div>
        <button class="preview-close">
          <i data-lucide="x" class="action-icon"></i>
        </button>
      </div>
      <div class="preview-body"></div>
      <div class="preview-actions">
        <a href="#" class="preview-download">
          <i data-lucide="download" class="action-icon"></i>
          Download
        </a>
      </div>
    </div>
  `
  document.body.appendChild(previewModal)

  // Close preview modal when clicking outside content
  previewModal.addEventListener("click", (e) => {
    if (e.target === previewModal) {
      closePreviewModal()
    }
  })

  // Close preview modal when clicking close button
  previewModal.querySelector(".preview-close").addEventListener("click", closePreviewModal)

  // Handle admin search
  adminSearchButton.addEventListener("click", performAdminSearch)
  adminSearchInput.addEventListener("keypress", (e) => {
    if (e.key === "Enter") {
      performAdminSearch()
    }
  })

  // Add preview functionality to file table
  setupFilePreview()

  // Make table responsive
  makeTableResponsive()

  // Functions
  function performAdminSearch() {
    const keyword = adminSearchInput.value.trim().toLowerCase()

    if (keyword === "") {
      // Show all rows
      const rows = adminFileList.querySelectorAll("tr")
      rows.forEach((row) => {
        row.style.display = ""
      })
      return
    }

    // Filter rows
    const rows = adminFileList.querySelectorAll("tr")
    rows.forEach((row) => {
      const filename = row.getAttribute("data-filename")?.toLowerCase() || ""
      if (filename.includes(keyword)) {
        row.style.display = ""
      } else {
        row.style.display = "none"
      }
    })
  }

  function setupFilePreview() {
    // Add preview button to image files in the admin table
    const rows = adminFileList.querySelectorAll("tr")
    rows.forEach((row) => {
      const fileTypeCell = row.querySelector("td:nth-child(4)")
      const actionsCell = row.querySelector("td:last-child .action-buttons")

      if (fileTypeCell && actionsCell && fileTypeCell.textContent.includes("image")) {
        const fileId = row.querySelector("td:first-child").textContent
        const filename = row.getAttribute("data-filename")
        const filePath = `uploads/${row.querySelector("td:nth-child(2)").getAttribute("data-filename") || ""}`

        // Create preview button
        const previewButton = document.createElement("button")
        previewButton.className = "action-button"
        previewButton.title = "Preview"
        previewButton.innerHTML = '<i data-lucide="eye" class="action-icon"></i>'
        previewButton.style.backgroundColor = "#10b981"
        previewButton.style.color = "white"

        previewButton.addEventListener("click", () => {
          openPreviewModal({
            id: fileId,
            original_filename: filename,
            file_path: filePath,
            file_type: fileTypeCell.textContent,
          })
        })

        // Insert after download button
        const downloadButton = actionsCell.querySelector(".download-button")
        if (downloadButton) {
          actionsCell.insertBefore(previewButton, downloadButton.nextSibling)
        } else {
          actionsCell.prepend(previewButton)
        }
      }
    })

    // Initialize icons
    if (typeof lucide !== "undefined") {
      lucide.createIcons()
    }
  }

  function makeTableResponsive() {
    // Add data attributes to cells for mobile view
    const headerCells = document.querySelectorAll(".file-table th")
    const headerTexts = Array.from(headerCells).map((cell) => cell.textContent)

    const rows = adminFileList.querySelectorAll("tr")
    rows.forEach((row) => {
      const cells = row.querySelectorAll("td")
      cells.forEach((cell, index) => {
        if (headerTexts[index]) {
          cell.setAttribute("data-label", headerTexts[index])
        }
      })
    })
  }

  function openPreviewModal(file) {
    const previewTitle = previewModal.querySelector(".preview-title")
    const previewBody = previewModal.querySelector(".preview-body")
    const previewDownload = previewModal.querySelector(".preview-download")

    // Set title
    previewTitle.textContent = file.original_filename

    // Set download link
    previewDownload.href = `download.php?id=${file.id}`

    // Clear previous content
    previewBody.innerHTML = ""

    // Add content based on file type
    if (file.file_type.includes("image")) {
      const img = document.createElement("img")
      img.src = file.file_path
      img.alt = file.original_filename
      img.className = "preview-image"
      previewBody.appendChild(img)
    } else {
      // For non-image files, show file info
      previewBody.innerHTML = `
        <div class="preview-file-info">
          <i data-lucide="file" class="preview-file-icon"></i>
          <h3>${file.original_filename}</h3>
          <p>Type: ${file.file_type}</p>
        </div>
      `
    }

    // Show modal
    previewModal.classList.add("active")

    // Initialize icons
    if (typeof lucide !== "undefined") {
      lucide.createIcons()
    }
  }

  function closePreviewModal() {
    previewModal.classList.remove("active")
  }

  function showToast(title, message, type = "info") {
    const toast = document.createElement("div")
    toast.className = `toast toast-${type}`

    let iconName = "info"
    if (type === "success") iconName = "check-circle"
    if (type === "error") iconName = "alert-circle"

    toast.innerHTML = `
      <i data-lucide="${iconName}" class="toast-icon"></i>
      <div class="toast-content">
        <div class="toast-title">${title}</div>
        <div class="toast-message">${message}</div>
      </div>
      <button class="toast-close">
        <i data-lucide="x" class="action-icon"></i>
      </button>
    `

    // Add to container
    toastContainer.appendChild(toast)

    // Initialize icons
    if (typeof lucide !== "undefined") {
      lucide.createIcons()
    }

    // Show toast with animation
    setTimeout(() => {
      toast.classList.add("show")
    }, 10)

    // Add close button functionality
    toast.querySelector(".toast-close").addEventListener("click", () => {
      toast.classList.remove("show")
      setTimeout(() => {
        toast.remove()
      }, 300)
    })

    // Auto remove after 5 seconds
    setTimeout(() => {
      if (toast.parentNode) {
        toast.classList.remove("show")
        setTimeout(() => {
          if (toast.parentNode) {
            toast.remove()
          }
        }, 300)
      }
    }, 5000)
  }
})
