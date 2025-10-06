document.addEventListener("DOMContentLoaded", () => {
  // Declare lucide if it's not already defined
  if (typeof lucide === "undefined") {
    window.lucide = {
      createIcons: () => {
        console.warn("Lucide is not defined. Using a mock implementation. Make sure it is properly imported.")
      },
    }
  }

  // Elements
  const dropArea = document.getElementById("drop-area")
  const fileInput = document.getElementById("fileInput")
  const uploadProgress = document.getElementById("upload-progress")
  const progressFill = document.getElementById("progress-fill")
  const progressText = document.getElementById("progress-text")
  const fileList = document.getElementById("file-list")
  const searchInput = document.getElementById("searchInput")
  const searchButton = document.getElementById("searchButton")
  const searchResults = document.getElementById("search-results")

  // Create toast container
  const toastContainer = document.createElement("div")
  toastContainer.className = "toast-container"
  document.body.appendChild(toastContainer)

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
  previewModal
    .querySelector(".preview-close")
    .addEventListener("click", closePreviewModal)

  // Prevent default drag behaviors
  ;["dragenter", "dragover", "dragleave", "drop"].forEach((eventName) => {
    dropArea.addEventListener(eventName, preventDefaults, false)
    document.body.addEventListener(eventName, preventDefaults, false)
  })

  // Highlight drop area when item is dragged over it
  ;["dragenter", "dragover"].forEach((eventName) => {
    dropArea.addEventListener(eventName, highlight, false)
  })
  ;["dragleave", "drop"].forEach((eventName) => {
    dropArea.addEventListener(eventName, unhighlight, false)
  })

  // Handle dropped files
  dropArea.addEventListener("drop", handleDrop, false)

  // Handle file input change
  fileInput.addEventListener("change", handleFiles, false)

  // Handle click on drop area
  dropArea.addEventListener("click", () => {
    fileInput.click()
  })

  // Handle search
  searchButton.addEventListener("click", performSearch)
  searchInput.addEventListener("keypress", (e) => {
    if (e.key === "Enter") {
      performSearch()
    }
  })

  // Add file upload restrictions notice
  const restrictionsNotice = document.createElement("div")
  restrictionsNotice.className = "upload-restrictions"
  restrictionsNotice.innerHTML = `
    <strong>Upload Guidelines:</strong>
    <ul>
      <li>Maximum file size: 50MB</li>
      <li>Allowed file types: Images, Documents, PDFs, Archives, Audio, Video</li>
      <li>Files are scanned for viruses before being made available</li>
    </ul>
  `
  dropArea.insertAdjacentElement("afterend", restrictionsNotice)

  // Load files on page load
  loadFiles()

  // Functions
  function preventDefaults(e) {
    e.preventDefault()
    e.stopPropagation()
  }

  function highlight() {
    dropArea.classList.add("highlight")
  }

  function unhighlight() {
    dropArea.classList.remove("highlight")
  }

  function handleDrop(e) {
    const dt = e.dataTransfer
    const files = dt.files
    handleFiles({ target: { files: files } })
  }

  function handleFiles(e) {
    const files = e.target.files
    if (files.length > 0) {
      // Validate files
      const validFiles = Array.from(files).filter(validateFile)

      if (validFiles.length > 0) {
        uploadFiles(validFiles)
      } else {
        showToast("Error", "No valid files to upload. Please check file types and sizes.", "error")
      }
    }
  }

  function validateFile(file) {
    // Check file size (50MB max)
    const maxSize = 50 * 1024 * 1024 // 50MB in bytes
    if (file.size > maxSize) {
      showToast("File too large", `${file.name} exceeds the 50MB limit.`, "error")
      return false
    }

    // Check file type (optional - can be adjusted based on requirements)
    const allowedTypes = [
      "image/",
      "application/pdf",
      "application/msword",
      "application/vnd.openxmlformats-officedocument",
      "text/",
      "application/zip",
      "application/x-rar-compressed",
      "audio/",
      "video/",
    ]

    const isValidType = allowedTypes.some((type) => file.type.startsWith(type))
    if (!isValidType && file.type !== "") {
      showToast("Invalid file type", `${file.name} is not an allowed file type.`, "error")
      return false
    }

    return true
  }

  function uploadFiles(files) {
    // Show progress bar
    uploadProgress.classList.remove("hidden")
    progressFill.style.width = "0%"
    progressText.textContent = "Uploading: 0%"

    const formData = new FormData()

    for (let i = 0; i < files.length; i++) {
      formData.append("files[]", files[i])
    }

    const xhr = new XMLHttpRequest()

    xhr.upload.addEventListener("progress", (e) => {
      if (e.lengthComputable) {
        const percentComplete = Math.round((e.loaded / e.total) * 100)
        progressFill.style.width = percentComplete + "%"
        progressText.textContent = `Uploading: ${percentComplete}%`
      }
    })

    xhr.addEventListener("load", () => {
      if (xhr.status === 200) {
        try {
          const response = JSON.parse(xhr.responseText)
          if (response.success) {
            // Hide progress bar after a delay
            setTimeout(() => {
              uploadProgress.classList.add("hidden")
            }, 1000)

            // Reset file input
            fileInput.value = ""

            // Show success toast
            showToast("Upload Complete", `Successfully uploaded ${response.files.length} file(s).`, "success")

            // Load updated file list
            loadFiles()
          } else {
            showToast("Upload Failed", response.message, "error")
          }
        } catch (error) {
          showToast("Error", "Error parsing server response", "error")
        }
      } else {
        showToast("Upload Failed", `Server returned status: ${xhr.status}`, "error")
      }
    })

    xhr.addEventListener("error", () => {
      showToast("Upload Failed", "Network error occurred. Please try again.", "error")
      uploadProgress.classList.add("hidden")
    })

    xhr.open("POST", "upload.php", true)
    xhr.send(formData)
  }

  function loadFiles() {
    fetch("search.php?keyword=")
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          displayFiles(data.files, fileList)
        }
      })
      .catch((error) => {
        console.error("Error loading files:", error)
        showToast("Error", "Failed to load files. Please refresh the page.", "error")
      })
  }

  function performSearch() {
    const keyword = searchInput.value.trim()

    if (keyword === "") {
      searchResults.innerHTML = "<p>Please enter a search term</p>"
      return
    }

    fetch(`search.php?keyword=${encodeURIComponent(keyword)}`)
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          if (data.files.length > 0) {
            searchResults.innerHTML = "<h4>Search Results:</h4>"
            const resultsContainer = document.createElement("div")
            resultsContainer.className = "file-list"
            searchResults.appendChild(resultsContainer)
            displayFiles(data.files, resultsContainer)
          } else {
            searchResults.innerHTML = "<p>No files found matching your search</p>"
          }
        } else {
          searchResults.innerHTML = "<p>Error performing search</p>"
        }
      })
      .catch((error) => {
        console.error("Error searching files:", error)
        searchResults.innerHTML = "<p>Error performing search</p>"
      })
  }

  function displayFiles(files, container) {
    container.innerHTML = ""

    if (files.length === 0) {
      container.innerHTML = "<p>No files found</p>"
      return
    }

    files.forEach((file) => {
      const fileItem = document.createElement("div")
      fileItem.className = "file-item"

      const fileInfo = document.createElement("div")
      fileInfo.className = "file-info"

      // Determine icon and file type class based on file type
      let iconName = "file"
      let fileTypeClass = "file-type-other"
      let fileTypeLabel = "File"

      if (file.file_type.includes("image")) {
        iconName = "image"
        fileTypeClass = "file-type-image"
        fileTypeLabel = "Image"
      } else if (
        file.file_type.includes("pdf") ||
        file.file_type.includes("document") ||
        file.file_type.includes("msword") ||
        file.file_type.includes("text")
      ) {
        iconName = "file-text"
        fileTypeClass = "file-type-document"
        fileTypeLabel = "Document"
      } else if (file.file_type.includes("video")) {
        iconName = "video"
        fileTypeClass = "file-type-video"
        fileTypeLabel = "Video"
      } else if (file.file_type.includes("audio")) {
        iconName = "music"
        fileTypeClass = "file-type-audio"
        fileTypeLabel = "Audio"
      } else if (
        file.file_type.includes("zip") ||
        file.file_type.includes("rar") ||
        file.file_type.includes("archive")
      ) {
        iconName = "archive"
        fileTypeClass = "file-type-archive"
        fileTypeLabel = "Archive"
      }

      fileInfo.innerHTML = `
        <i data-lucide="${iconName}" class="file-icon"></i>
        <span class="file-name">${file.original_filename}</span>
        <span class="file-type-indicator ${fileTypeClass}">${fileTypeLabel}</span>
      `

      const fileMeta = document.createElement("div")
      fileMeta.className = "file-meta"
      fileMeta.innerHTML = `
        <span>${formatFileSize(file.file_size)}</span>
        <span>${formatDate(file.upload_date)}</span>
      `

      const fileActions = document.createElement("div")
      fileActions.className = "file-actions"

      // Add preview button for images
      if (file.file_type.includes("image")) {
        const previewButton = document.createElement("button")
        previewButton.className = "file-preview"
        previewButton.innerHTML = `
          <i data-lucide="eye" class="preview-icon"></i>
          Preview
        `
        previewButton.addEventListener("click", () => {
          openPreviewModal(file)
        })
        fileActions.appendChild(previewButton)
      }

      // Add download button
      const downloadLink = document.createElement("a")
      downloadLink.href = `download.php?id=${file.id}`
      downloadLink.className = "file-download"
      downloadLink.innerHTML = `
        <i data-lucide="download" class="download-icon"></i>
        Download
      `
      fileActions.appendChild(downloadLink)

      fileItem.appendChild(fileInfo)
      fileItem.appendChild(fileMeta)
      fileItem.appendChild(fileActions)
      container.appendChild(fileItem)
    })

    // Initialize Lucide icons for the newly added elements
    if (typeof lucide !== "undefined") {
      lucide.createIcons()
    } else {
      console.warn("Lucide is not defined. Make sure it is properly imported.")
    }
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
          <p>Size: ${formatFileSize(file.file_size)}</p>
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

  function formatFileSize(bytes) {
    if (bytes >= 1073741824) {
      return (bytes / 1073741824).toFixed(2) + " GB"
    } else if (bytes >= 1048576) {
      return (bytes / 1048576).toFixed(2) + " MB"
    } else if (bytes >= 1024) {
      return (bytes / 1024).toFixed(2) + " KB"
    } else {
      return bytes + " bytes"
    }
  }

  function formatDate(dateString) {
    const date = new Date(dateString)
    return date.toLocaleDateString() + " " + date.toLocaleTimeString()
  }
})
