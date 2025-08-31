<footer class="bg-dark text-white mt-5">
    <div class="container py-5">
        <div class="row">
            <!-- About Section -->
            <div class="col-lg-4 col-md-6 mb-4">
                <h5 class="mb-3">
                    <i class="bi bi-cpu-fill"></i> FirmwareHub
                </h5>
                <p class="text-white-50">
                    Your trusted source for mobile firmware, ROMs, and tools. 
                    Download with confidence from our extensive collection.
                </p>
                <div class="mt-3">
                    <a href="#" class="text-white-50 me-3"><i class="bi bi-facebook fs-5"></i></a>
                    <a href="#" class="text-white-50 me-3"><i class="bi bi-twitter fs-5"></i></a>
                    <a href="#" class="text-white-50 me-3"><i class="bi bi-instagram fs-5"></i></a>
                    <a href="#" class="text-white-50"><i class="bi bi-youtube fs-5"></i></a>
                </div>
            </div>
            
            <!-- Quick Links -->
            <div class="col-lg-2 col-md-6 mb-4">
                <h6 class="mb-3">Quick Links</h6>
                <ul class="list-unstyled">
                    <li class="mb-2">
                        <a href="/products" class="text-white-50 text-decoration-none">
                            <i class="bi bi-chevron-right small"></i> Browse Files
                        </a>
                    </li>
                    <li class="mb-2">
                        <a href="/packages" class="text-white-50 text-decoration-none">
                            <i class="bi bi-chevron-right small"></i> Packages
                        </a>
                    </li>
                    <li class="mb-2">
                        <a href="/brands" class="text-white-50 text-decoration-none">
                            <i class="bi bi-chevron-right small"></i> Brands
                        </a>
                    </li>
                    <li class="mb-2">
                        <a href="/tools" class="text-white-50 text-decoration-none">
                            <i class="bi bi-chevron-right small"></i> Tools
                        </a>
                    </li>
                </ul>
            </div>
            
            <!-- Support -->
            <div class="col-lg-2 col-md-6 mb-4">
                <h6 class="mb-3">Support</h6>
                <ul class="list-unstyled">
                    <li class="mb-2">
                        <a href="/help" class="text-white-50 text-decoration-none">
                            <i class="bi bi-chevron-right small"></i> Help Center
                        </a>
                    </li>
                    <li class="mb-2">
                        <a href="/faq" class="text-white-50 text-decoration-none">
                            <i class="bi bi-chevron-right small"></i> FAQ
                        </a>
                    </li>
                    <li class="mb-2">
                        <a href="/contact" class="text-white-50 text-decoration-none">
                            <i class="bi bi-chevron-right small"></i> Contact Us
                        </a>
                    </li>
                    <li class="mb-2">
                        <a href="/tutorials" class="text-white-50 text-decoration-none">
                            <i class="bi bi-chevron-right small"></i> Tutorials
                        </a>
                    </li>
                </ul>
            </div>
            
            <!-- Legal -->
            <div class="col-lg-2 col-md-6 mb-4">
                <h6 class="mb-3">Legal</h6>
                <ul class="list-unstyled">
                    <li class="mb-2">
                        <a href="/terms" class="text-white-50 text-decoration-none">
                            <i class="bi bi-chevron-right small"></i> Terms of Service
                        </a>
                    </li>
                    <li class="mb-2">
                        <a href="/privacy" class="text-white-50 text-decoration-none">
                            <i class="bi bi-chevron-right small"></i> Privacy Policy
                        </a>
                    </li>
                    <li class="mb-2">
                        <a href="/refund" class="text-white-50 text-decoration-none">
                            <i class="bi bi-chevron-right small"></i> Refund Policy
                        </a>
                    </li>
                    <li class="mb-2">
                        <a href="/dmca" class="text-white-50 text-decoration-none">
                            <i class="bi bi-chevron-right small"></i> DMCA
                        </a>
                    </li>
                </ul>
            </div>
            
            <!-- Newsletter -->
            <div class="col-lg-2 col-md-12 mb-4">
                <h6 class="mb-3">Newsletter</h6>
                <p class="text-white-50 small">Subscribe for updates and exclusive offers</p>
                <form action="/newsletter" method="POST">
                    @csrf
                    <div class="input-group input-group-sm">
                        <input type="email" class="form-control" placeholder="Email" name="email" required>
                        <button class="btn btn-primary" type="submit">
                            <i class="bi bi-send"></i>
                        </button>
                    </div>
                </form>
                
                <!-- Payment Methods -->
                <div class="mt-4">
                    <h6 class="small mb-2">We Accept</h6>
                    <div class="d-flex">
                        <i class="bi bi-credit-card fs-4 text-white-50 me-2"></i>
                        <i class="bi bi-paypal fs-4 text-white-50 me-2"></i>
                        <i class="bi bi-stripe fs-4 text-white-50"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Bottom Bar -->
        <hr class="text-white-50">
        <div class="row align-items-center">
            <div class="col-md-6">
                <p class="mb-0 text-white-50 small">
                    &copy; {{ date('Y') }} FirmwareHub. All rights reserved.
                </p>
            </div>
            <div class="col-md-6 text-md-end">
                <p class="mb-0 text-white-50 small">
                    <i class="bi bi-shield-check"></i> Secure Downloads |
                    <i class="bi bi-lightning"></i> Fast CDN |
                    <i class="bi bi-headset"></i> 24/7 Support
                </p>
            </div>
        </div>
    </div>
    
    <!-- Back to Top Button -->
    <button onclick="window.scrollTo({top: 0, behavior: 'smooth'})" 
            class="btn btn-primary btn-sm position-fixed bottom-0 end-0 m-3" 
            style="display: none; z-index: 1000;" id="backToTop">
        <i class="bi bi-arrow-up"></i>
    </button>
</footer>

<!-- Back to Top Script -->
<script>
    window.addEventListener('scroll', function() {
        const backToTop = document.getElementById('backToTop');
        if (window.pageYOffset > 300) {
            backToTop.style.display = 'block';
        } else {
            backToTop.style.display = 'none';
        }
    });
</script>