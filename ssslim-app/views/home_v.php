<style>
    iframe {
        border: 5px solid #000;
        border-radius: 5px
    }
    .fullscreenChat {
        position: fixed;
        right: 0;
        bottom: 0;
        width: 300px;
        height: 300px;
        z-index: 999;
    }
    .fullscreenChat iframe {
        height: 100%
    }
    #closeBtn {
        position: fixed;
        top: 10px;
        right: 10px;
        height: 50px;
        width: 50px;
        text-align: center;
        font-size: 25px;
        border-radius: 50%;
        z-index: 999;
        display: none;
    }
    #chatWrp {
        position:relative;
    }
    #clickCatcher {
        position: absolute;
        top: 0;
        left: 0;
    }
    .social .home {
        display: none;
    }
    #comingsoon {
        position: relative;
        background: rgba(43, 42, 44, .9) !important;
    }
    #comingsoon div {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        display: flex;
        justify-content: center;
        align-items: center;
    }
    #comingsoon p {
        color: #000;
        text-transform: capitalize;
        padding: 4px 7px;
        background: #fff;
        letter-spacing: 3px;
    }

    @media only screen and (min-width: 768px) {
        
        .fullscreenChat {
            background: rgba(7, 49, 64, .6);
            justify-content: center;
            align-items: center;
            display: flex;
        }
    
        .fullscreenChat iframe {
            height: 80%;
            width: 60%;
        }
    }
</style>

<section id="your-clouds">
    <div class="max" style="align-items: center">
        <div>
            <div class="logos">
                <div class="logo ibm"><span>IBM</span></div>
                <div class="logo futurism"><span>Futurism</span></div>
            </div>
            <h1>
                Your Clouds Can <span>2019</span>
            </h1>
            <p>
                Experience the future of cloud-based technology for consumer companies
            </p>
        </div>
        <div style="flex:2">
            <iframe width="100%" height="400" src="https://www.youtube.com/embed/07VrhhPyjes" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
        </div>
    </div>
</section>

<!-- TAKEAWAYS -->
<section id="takeaways">
    <div class="max">
        <div>
            <img src="<?= base_url() ?>images/gated_article_thumb.jpg" alt="Get the key takeaways from the day">
        </div>
        <div>
            <h2>Get the key takeaways from the day</h2>
            <p>
                We heard from visionary leaders, data-driven innovators, and entrepreneurs who are scaling their businesses leveraging cloud-based technologies.
            </p>
            <a class="button" href="<?= site_url() ?>download-key-takeaways">Download Key Takeaways</a>
        </div>
    </div>
</section>

<!-- COACH -->
<section id="coach">
    <div class="max">
        <div>
            <img src="<?= base_url() ?>wp-content\themes\futurism\img\coach.png" alt="">
        </div>
        <div>
            <p>
                <b>Talk to our Cloud Coach and get your customized playbook</b> for your company’s journey to leveraging cloud-based technologies
            </p>
            <div id="chatWrp">
                <div id="clickCatcher" style="height:300px"></div>
                <div id="querloEmbd" class="querlo">
                    <iframe src="https://www.querlo.com/chat/YCCcloudcoach<?= !empty($email) ? '?EMAIL=' . $email : '' ?>" width="100%" height="300" frameborder="0"></iframe>
                </div>
            </div>
            <br />
            <button id="tryme">Try it!</button>
        </div>
    </div>
</section>

<!-- QUOTE -->
<section id="quote">
    <div class="max">
        <div id="quote-profile">
            <div class="profile-img"><img src="<?= base_url() ?>images/selena_lounds.jpg" alt="Selena Lounds" /></div>
            <h2>Selena Lounds</h2>
            <h3>Senior Manager of Design Technology, Tapestry</h3>
        </div>
        <div id="quote-text" class="firstQuoteText">
            <p>
                “To be able to go to BuzzFeed and to be in a lab at Betaworks was really amazing.”
            </p>
        </div>
        <div id="quote-profile">
            <div class="profile-img"><img src="<?= base_url() ?>images/vlad_shenderovich.jpg" alt="Vlad Shenderovich" /></div>
            <h2>Vlad Shenderovich</h2>
            <h3>Director of Operations, LOLI Beauty</h3>
        </div>
        <div id="quote-text">
            <p>
                “There’s a lot to take away from it and I’m probably going to spend a lot of time unpacking and digesting it… It’s super fun. I would do it again in a heartbeat and if it happens again next year I would love to get an invite.”
            </p>
        </div>
    </div>
</section>

<!-- DIVE DEEPER -->
<section id='dive-deeper'>
    <div class="max">
        <h2>
            Dive deeper into
            <span>your clouds can 2019</span>
        </h2>
        <div id="videos">
            <article>
                <a class="article-link" href="videos\video-post-2\index.htm" title="Video Post #2">
                    <div class="details">
                        <h3>How can you discover the next big innovation?</h3>
                        <div class="excerpt">
                            <p>Data-driven ways innovators like Buzzfeed and coming up with new products.</p>
                        </div>
                    </div>
                    <div class="thumb-holder">
                        <div class="thumb" aria-label="How can you discover the next big innovation?" style="background-image: url(wp-content/uploads/2019/06/fpo-video.png);"></div>						</div>
                </a>
            </article>
            <article>
                <span class="article-link" href="videos\video-post-1\index.htm" title="Video Post #1">
                    <div class="details">
                        <h3>More videos coming soon...</h3>
                        <div class="excerpt">
                            <p>Check back next week for new videos on Your clouds can 2019</p>
                        </div>
                    </div>
                    <div class="thumb-holder">
                        <div class="thumb" aria-label="Video Post #1" id="comingsoon">
                            <div>
                                <p>coming soon</p>
                            </div>
                            
                        </div>
                    </div>
                </span>
            </article>
        </div>
        <?/*<div id="articles">
            <article>
                <a class="article-link" href="2019\06\02\this-is-a-test-post-with-a-long-title-4\index.htm" title="This is a test post with a long title #4">
                    <div class="details">
                        <h3>
                            This is a test post with a long title #4				                </h3>
                        <div class="excerpt">
                            <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit.</p>
                        </div>
                    </div>
                    <div class="thumb-holder">
                        <div class="thumb" aria-label="This is a test post with a long title #4" style="background-image: url(wp-content/uploads/2019/06/fpo-article.png);"></div>							</div>
                </a>
            </article>
            <article>
                <a class="article-link" href="2019\06\02\test-post-3\index.htm" title="Test post #3">
                    <div class="details">
                        <h3>
                            Test post #3				                </h3>
                        <div class="excerpt">
                            <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit.</p>
                        </div>
                    </div>
                    <div class="thumb-holder">
                        <div class="thumb" aria-label="Test post #3" style="background-image: url(wp-content/uploads/2019/06/fpo-article.png);"></div>							</div>
                </a>
            </article>
            <article>
                <a class="article-link" href="2019\06\02\this-is-a-test-post-2\index.htm" title="This is a test post #2">
                    <div class="details">
                        <h3>
                            This is a test post #2				                </h3>
                        <div class="excerpt">
                            <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit.</p>
                        </div>
                    </div>
                    <div class="thumb-holder">
                        <div class="thumb" aria-label="This is a test post #2" style="background-image: url(wp-content/uploads/2019/06/fpo-article.png);"></div>							</div>
                </a>
            </article>
            <article>
                <a class="article-link" href="2019\06\02\this-is-a-test-post\index.htm" title="This is a test post #1">
                    <div class="details">
                        <h3>
                            This is a test post #1				                </h3>
                        <div class="excerpt">
                            <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit.</p>
                        </div>
                    </div>
                    <div class="thumb-holder">
                        <div class="thumb" aria-label="This is a test post #1" style="background-image: url(wp-content/uploads/2019/06/fpo-article.png);"></div>							</div>
                </a>
            </article>
        </div>
    </div>*/?>
        <button id="closeBtn" class="button">X</button>
</section>

<script>
  $(function() {
    var closeBtn = $("#closeBtn");

    $('#clickCatcher').css({
      width: $("#querloEmbd").width()
    });

    $('#tryme, #clickCatcher').on("click", function() {
      $("#querloEmbd").addClass("fullscreenChat");
      $("#querloEmbd").animate({
        width: "100%",
        height: "100%"
      });
      $('#clickCatcher').hide();
      closeBtn.show();
    });

    closeBtn.on("click", function() {
      $("#querloEmbd").removeClass("fullscreenChat").css({width: '350px', height: '300px'});
      closeBtn.hide();
      $('#clickCatcher').show();
    })
  });
</script>