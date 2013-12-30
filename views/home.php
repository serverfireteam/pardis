<?php require("head.php"); ?>
<a href="?page=register" class="link-no-decoration">
	<section class="advertise hbox full-width">
	    <aside class="advertise-text">
	        <article class="text-n-1"><h3 class="bold-title">Vuoi diventare tester ufficiale dei prodotti LG?</h3></article>
            <article class="text-n-2"><h4 class="normal-title">Compila il form di registrazione e scegli la tecnologia LG che vuoi provare,</h4></article>
            <article class="text-n-3"><h5 style="color:#fff">tra tutti gli iscritti verranno scelti alcuni tester che riceveranno direttamente a casa il prodotto da testare!</h5></article>
        </aside>
        <aside class="advertise-logo">
            <article class="advertise-logo"><img src="img/advertise-logo.png"/></article>
        </aside>
        <aside class="advertise-arrow asdie-arrow">
	        <article class="advertise-arrow"><img src="img/arrow-right.png" /></article>
        </aside>
	</section>
</a>
  <div class="subpages-slider-wrp">
    <div class="subpages-slider-wrp2">
      <ul class="subpages-slider">
        <?php foreach($products as $product): ?>
        <li>
          <img class="page-pic" src="<?=$product['image_src'];?>" alt=""/>
          <h3 class="p-title"><?=$product['tester_title'];?></h3><br /><br /><br />
          <div class="description">
            <article class="title-box big-title"><span class="bold-title"><?=$product["short_description"];?></span></article>
    	    <div class="p-btn-group">
              <a class="p-btn" href="?page=product&id=<?=$product["id"];?>">SCOPRI DI PIÙ</a>
              <a class="p-btn p-btn-magenta" href="?page=register&id=<?=$product["id"];?>">TESTA QUESTO PRODOTTO</A>
            </div>
            <article class="muted-text-box">
	      <a class="muted-text" href="?page=product">Scopri tutti i prodotti che puoi testare »</a>
	    </article>
          </div>
        </li>
        <?php endforeach; ?>
      </ul>
    </div>
    <div class="pager-bg"></div>
  </div>

    <!-- Mini Box -->
    <section class="mini-box hbox full-width">
	    <aside class="box">
	        <article class="header"><h3 class="normal-title">Cosa deve fare un tester</h3></article>
            <article class="text">
	            <ul>
					<li style="list-style-type:disc;color: #b91f55;"><span style="font-family: arial,helvetica,sans-serif;"><span style="color: #4c4c4c;"><span style="font-size: small;">Scegli il prodotto che vuoi testare<br /></span></span></span></li>
					<li style="list-style-type:disc;color: #b91f55;"><span style="font-family: arial,helvetica,sans-serif;"><span style="color: #4c4c4c;"><span style="font-size: small;">Compila il form di registrazione e inserisci la motivazione per cui vuoi diventare tester del prodotto LG per il quale ti stai candidando</span></span></span></li>
					<li style="list-style-type:disc;color: #b91f55;"><span style="font-family: arial,helvetica,sans-serif;"><span style="color: #4c4c4c;"><span style="font-size: small;">Attendi la mail di conferma<br /></span></span></span></li>
					<li style="list-style-type:disc;color: #b91f55;"><span style="font-family: arial,helvetica,sans-serif;"><span style="color: #4c4c4c;"><span style="font-size: small;">...potresti essere selezionata come tester da LG e alfemminile!<br /></span></span></span></li>
					<li style="list-style-type:disc;color: #b91f55;"><span style="font-family: arial,helvetica,sans-serif;"><span style="color: #4c4c4c;"><span style="font-size: small;">Al termine della prova il prodotto andrà restituito.<br /></span></span></span></li>
				</ul>
			</article>
            <article class="box-button"><a class="button" href="?page=come_funziona#fb-root">Informazioni Sull’iniziativa</a></article>
        </aside>
        <aside class="box red-box">
	        <article class="header"><h4 class="bold-title">I prodotti LG che puoi ricevere a casa tua</h4></article>
            <article class="text"><h5 class="normal-title">Pocket Photo, Frigorifero Side By Side, Lavatrice a vapore 9KG, Asciugabiancheria 9KG, Microonde LightWave 
Robot Aspirapolvere, Monitor IPS Personal TV LED 27'' - Smart TV 3D, Sound Plate.</h5></article>
            <article class="box-button text-center">
				<a class="button" href="?page=product#fb-root">Scopri tutti i prodotti che puoi testare</a>
			</article>
		</aside>
	</section>

</section> <!-- end of body -->

<?php require("footer.php"); ?>
