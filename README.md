# PoptejPlot.cz

Jednoduchý web pro poptávky na ploty a registrace dodavatelů.

## Co je součástí

- Responzivní úvodní stránka a mobilní navigace.
- Orientační počítadlo standardních panelů po 2,5 m a sloupků.
- Formulář poptávky a samostatná registrace dodavatele.
- Ochrana proti falešnému potvrzení: web oznámí přijetí až po odpovědi backendu.
- Informační stránky: ochrana osobních údajů, podmínky a cookies.
- PHP endpoint pro e-mailové předání na `info@poptejplot.cz` a `partners@poptejplot.cz`.

## Důležitá informace o hostingu

Aktuální nasazení repozitáře je GitHub Pages. GitHub Pages obsluhuje statické soubory a **nespouští PHP**. Stránky se zobrazí, ale endpoint `api/submit.php` tam nebude fungovat. Dokud je meta tag `form-endpoint` prázdný, formuláře vůbec neposílají zadaná data na server; nabídnou pouze ručně otevřený e-mail. Po přechodu na PHP hosting doplňte do obou HTML souborů hodnotu `/api/submit.php` do tohoto meta tagu.

Pro automatické odesílání je potřeba umístit web na PHP hosting (například WEDOS) nebo nasměrovat `api/submit.php` na samostatný PHP server se správně nastaveným HTTPS a CORS. V historii projektu je pro hosting uvedena cesta `/www/domains/poptejplot.cz`; před přepnutím domény ověřte aktuální DNS a cílový hosting. PHP endpoint neposílá ani neukládá žádné údaje, dokud není přítomný hostitelský soubor `api/config.php` a vyplněné údaje provozovatele.

## Zapnutí PHP endpointu na hostingu

1. Nahrajte statické soubory webu do kořene domény a složku `api` včetně `submit.php`.
2. Na hostingu vytvořte `api/config.php` z `api/config.example.php`. Tento soubor **necommitujte**.
3. Doplňte skutečný právní název provozovatele, IČO a sídlo do pole `operator`. Nechte zablokovaný endpoint, dokud nejsou právní údaje ověřené.
4. Nastavte `from_email` na funkční adresu na této doméně. Ověřte doručování a SPF/DKIM podle pokynů hostingu.
5. Otestujte oba formuláře na testovacích údajích. Potvrzení na stránce se zobrazí pouze po úspěšném předání zprávy poštovnímu serveru.
6. Před ostrým sběrem údajů doplňte a právně zkontrolujte stránku ochrany osobních údajů, zejména správce, zpracovatele, retenci a přesnou volbu pro předání kontaktu dodavateli.

Endpoint ověřuje metodu, JSON payload, původ požadavku, povinná pole a skryté honeypot pole. Odesílá prostý text přes PHP `mail()`; nezajišťuje databázi, CRM, automatické přiřazení poptávky, potvrzovací e-mail zákazníkovi ani automatickou distribuci dodavatelům. Doručení do schránky je nutné ověřit na konkrétním hostingu.

## Důležité obchodní a právní údaje

Není doplněný úplný právní provozovatel PoptejPlot.cz. Proto web neuvádí smyšlené IČO, adresu, reference, počty zakázek ani ceny. Právní informační stránky jasně označují, co je třeba před plným provozem doplnit. Nedoplňujte tyto údaje odhadem.

## Lokální kontrola

Statické stránky lze otevřít lokálním HTTP serverem. PHP endpoint otestujte pouze na PHP hostingu s testovacími adresami. Nikdy neposílejte skutečné osobní údaje do testovacího formuláře.
