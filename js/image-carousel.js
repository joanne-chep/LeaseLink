
function createPropertyCarousel(propertyId, images, containerId) {
    const container = document.getElementById(containerId) || document.querySelector(`[data-property-id="${propertyId}"] .property-image-carousel`);
    if (!container || !images || images.length === 0) return null;
    
    const carouselHtml = `
        <div class="property-image-carousel" onclick="openImageGallery(${propertyId}, ${JSON.stringify(images).replace(/"/g, '&quot;')})" style="cursor: pointer;">
            <div class="carousel-container" id="carousel-${propertyId}">
                ${images.map((img, index) => `
                    <div class="carousel-slide ${index === 0 ? 'active' : ''}">
                        <img src="${img.image_url || img}" alt="Property image ${index + 1}" loading="lazy">
                    </div>
                `).join('')}
            </div>
            ${images.length > 1 ? `
                <button class="carousel-controls carousel-prev" onclick="event.stopPropagation(); slideCarousel(${propertyId}, -1)">‹</button>
                <button class="carousel-controls carousel-next" onclick="event.stopPropagation(); slideCarousel(${propertyId}, 1)">›</button>
                <div class="carousel-indicators" id="indicators-${propertyId}">
                    ${images.map((_, index) => `
                        <button class="carousel-indicator ${index === 0 ? 'active' : ''}" onclick="event.stopPropagation(); goToSlide(${propertyId}, ${index})"></button>
                    `).join('')}
                </div>
                <div class="carousel-image-count">${images.length} images</div>
            ` : ''}
        </div>
    `;
    
    return carouselHtml;
}

let carouselIntervals = {};

function slideCarousel(propertyId, direction) {
    const carousel = document.getElementById(`carousel-${propertyId}`);
    if (!carousel) return;
    
    const slides = carousel.querySelectorAll('.carousel-slide');
    const indicators = document.querySelectorAll(`#indicators-${propertyId} .carousel-indicator`);
    if (slides.length <= 1) return;
    
    const currentIndex = Array.from(slides).findIndex(slide => slide.classList.contains('active'));
    let newIndex = currentIndex + direction;
    
    if (newIndex < 0) newIndex = slides.length - 1;
    if (newIndex >= slides.length) newIndex = 0;
    
    // Update slides
    slides[currentIndex].classList.remove('active');
    slides[newIndex].classList.add('active');
    
    // Update indicators
    indicators[currentIndex].classList.remove('active');
    indicators[newIndex].classList.add('active');
}

function goToSlide(propertyId, index) {
    const carousel = document.getElementById(`carousel-${propertyId}`);
    if (!carousel) return;
    
    const slides = carousel.querySelectorAll('.carousel-slide');
    const indicators = document.querySelectorAll(`#indicators-${propertyId} .carousel-indicator`);
    
    // Remove active from all
    slides.forEach(slide => slide.classList.remove('active'));
    indicators.forEach(ind => ind.classList.remove('active'));
    
    // Add active to selected
    slides[index].classList.add('active');
    indicators[index].classList.add('active');
}

// Auto-advance carousel (optional)
function startCarouselAutoPlay(propertyId, interval = 4000) {
    if (carouselIntervals[propertyId]) {
        clearInterval(carouselIntervals[propertyId]);
    }
    
    const carousel = document.getElementById(`carousel-${propertyId}`);
    if (!carousel) return;
    
    carouselIntervals[propertyId] = setInterval(() => {
        slideCarousel(propertyId, 1);
    }, interval);
    
    // Pause on hover
    carousel.closest('.property-image-carousel').addEventListener('mouseenter', () => {
        stopCarouselAutoPlay(propertyId);
    });
    
    // Resume on mouse leave
    carousel.closest('.property-image-carousel').addEventListener('mouseleave', () => {
        startCarouselAutoPlay(propertyId, interval);
    });
}

function stopCarouselAutoPlay(propertyId) {
    if (carouselIntervals[propertyId]) {
        clearInterval(carouselIntervals[propertyId]);
        delete carouselIntervals[propertyId];
    }
}

// Full Screen Image Gallery
let currentGalleryImages = [];
let currentGalleryIndex = 0;

function openImageGallery(propertyId, images) {
    // Convert string back to array 
    if (typeof images === 'string') {
        try {
            // Handle both escaped and unescaped JSON strings
            const cleanedString = images.replace(/&quot;/g, '"').replace(/'/g, '"');
            images = JSON.parse(cleanedString);
        } catch (e) {
            // If parsing fails, try fetching images from server
            console.warn('Error parsing images, fetching from server:', e);
            fetch(`backend/get_property_images.php?property_id=${propertyId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.images.length > 0) {
                        openImageGallery(propertyId, data.images);
                    }
                })
                .catch(err => console.error('Error fetching images:', err));
            return;
        }
    }
    
    // Handle case where images is an array but items might be objects
    if (Array.isArray(images) && images.length > 0) {
        // Normalize image objects
        images = images.map(img => {
            if (typeof img === 'string') {
                return { image_url: img, description: '', is_main: false };
            }
            return img;
        });
    }
    
    if (!images || images.length === 0) return;
    
    currentGalleryImages = images;
    currentGalleryIndex = 0;
    
    // Create or get modal
    let modal = document.getElementById('imageGalleryModal');
    if (!modal) {
        modal = document.createElement('div');
        modal.id = 'imageGalleryModal';
        modal.className = 'image-gallery-modal';
        modal.innerHTML = `
            <span class="gallery-close" onclick="closeImageGallery()">&times;</span>
            <div class="gallery-counter" id="galleryCounter">1 / ${images.length}</div>
            <button class="gallery-controls gallery-prev" onclick="galleryPrevious()">‹</button>
            <img class="gallery-main-image" id="galleryMainImage" src="" alt="Property image">
            <button class="gallery-controls gallery-next" onclick="galleryNext()">›</button>
            <div class="gallery-thumbnails" id="galleryThumbnails"></div>
        `;
        document.body.appendChild(modal);
    }
    
    // Update modal content
    updateGalleryImage();
    updateGalleryThumbnails();
    
    // Show modal
    modal.classList.add('active');
    document.body.style.overflow = 'hidden';
    
    document.addEventListener('keydown', handleGalleryKeyboard);
}

function closeImageGallery() {
    const modal = document.getElementById('imageGalleryModal');
    if (modal) {
        modal.classList.remove('active');
        document.body.style.overflow = '';
    }
    document.removeEventListener('keydown', handleGalleryKeyboard);
}

function updateGalleryImage() {
    if (currentGalleryImages.length === 0) return;
    
    const img = currentGalleryImages[currentGalleryIndex];
    const mainImage = document.getElementById('galleryMainImage');
    const counter = document.getElementById('galleryCounter');
    
    if (mainImage) {
        mainImage.src = img.image_url || img;
        mainImage.alt = img.description || `Property image ${currentGalleryIndex + 1}`;
    }
    
    if (counter) {
        counter.textContent = `${currentGalleryIndex + 1} / ${currentGalleryImages.length}`;
    }
}

function updateGalleryThumbnails() {
    const container = document.getElementById('galleryThumbnails');
    if (!container) return;
    
    container.innerHTML = currentGalleryImages.map((img, index) => `
        <img class="gallery-thumbnail ${index === currentGalleryIndex ? 'active' : ''}" 
             src="${img.image_url || img}" 
             alt="Thumbnail ${index + 1}"
             onclick="galleryGoToImage(${index})">
    `).join('');
}

function galleryNext() {
    currentGalleryIndex = (currentGalleryIndex + 1) % currentGalleryImages.length;
    updateGalleryImage();
    updateGalleryThumbnails();
}

function galleryPrevious() {
    currentGalleryIndex = (currentGalleryIndex - 1 + currentGalleryImages.length) % currentGalleryImages.length;
    updateGalleryImage();
    updateGalleryThumbnails();
}

function galleryGoToImage(index) {
    currentGalleryIndex = index;
    updateGalleryImage();
    updateGalleryThumbnails();
}

function handleGalleryKeyboard(event) {
    if (event.key === 'ArrowLeft') {
        galleryPrevious();
    } else if (event.key === 'ArrowRight') {
        galleryNext();
    } else if (event.key === 'Escape') {
        closeImageGallery();
    }
}

// Close modal when clicking outside the image
document.addEventListener('DOMContentLoaded', () => {
    document.addEventListener('click', (e) => {
        const modal = document.getElementById('imageGalleryModal');
        if (modal && e.target === modal) {
            closeImageGallery();
        }
    });
});

