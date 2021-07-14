<style>
    main {
        position: relative;
    }
    iframe {
        position: absolute;
        top: 0;
        left: 0;
        height: 100%;
        width: 100%
    }
    .down {
        position: absolute;
        top: 13px;
        left: 0;
        background: rgba(0,0,0,.8)
    }
    .down a {
        height: 24px;
        padding: 12px;
        margin: 8px 10px 6px 9px!important;
    }
    .down:hover {
        background: rgba(0,0,0,.5)
    }
</style>
<iframe src="<?= $pdfUrl ?>" frameborder="0"></iframe>
<div class="social down">
    <a href="<?= site_url() ?>download/<?= $id ?>" class="download"><span>Download</span></a>
</div>