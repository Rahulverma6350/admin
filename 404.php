<?php include('header.php')?>

    <main class="main__content_wrapper">
        
        <!-- Start error section -->
        <section class="error__section section--padding">
            <div class="container">
                <div class="row row-cols-1">
                    <div class="col">
                        <div class="error__content text-center">
                            <img class="error__content--img mb-50" src="assets/img/other/404-thumb.webp" alt="error-img">
                            <h2 class="error__content--title">Opps ! We're not found this page  </h2>
                            <p class="error__content--desc">Lorem, ipsum dolor sit amet consectetur adipisicing elit. Excepturi animi aliquid minima assumenda.</p>
                            <a class="error__content--btn primary__btn" href="index.php">Back To Home</a>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- End error section -->

        <!-- Start Newsletter banner section -->
        <section class="newsletter__banner--section section--padding">
            <div class="container">
                <div class="newsletter__banner--thumbnail position__relative">
                    <img class="newsletter__banner--thumbnail__img" src="assets/img/banner/banner-bg7.webp" alt="newsletter-banner">
                    <div class="newsletter__content newsletter__subscribe">
                        <h5 class="newsletter__content--subtitle text-white">Want to offer regularly ?</h5>
                        <h2 class="newsletter__content--title text-white h3 mb-25">Subscribe Our Newsletter <br>
                            for Get Daily Update</h2>
                        <form class="newsletter__subscribe--form position__relative" action="#">
                            <label>
                                <input class="newsletter__subscribe--input" placeholder="Enter your email address" type="email">
                            </label>
                            <button class="newsletter__subscribe--button primary__btn" type="submit">Subscribe
                                <svg class="newsletter__subscribe--button__icon" xmlns="http://www.w3.org/2000/svg" width="9.159" height="7.85" viewbox="0 0 9.159 7.85">
                                    <path data-name="Icon material-send" d="M3,12.35l9.154-3.925L3,4.5,3,7.553l6.542.872L3,9.3Z" transform="translate(-3 -4.5)" fill="currentColor"></path>
                                </svg>
                            </button>
                        </form>   
                    </div>
                </div>
            </div>
        </section>
        <!-- End Newsletter banner section -->

    </main>

<?php include('footer.php')?>
