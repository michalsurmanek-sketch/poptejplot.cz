# PoptejPlot.cz

Jednoduchý web pro poptávky na ploty a registrace dodavatelů.

## Co je součástí

- Responzivní úvodní stránka a mobilní navigace.
- Orientační počítadlo standardních panelů po 2,5 m a sloupků.
- Formulář poptávky a samostatná registrace dodavatele.
- Ochrana proti falešnému potvrzení: web oznámí přijetí až po odpovědi backendu.
- Informační stránky: ochrana osobních údajů, podmínky a cookies.
- Bezpečné uložení poptávek do administrace PLOTAO.cz.

## Napojení poptávky na administraci PLOTAO.cz

Poptávka z `index.html` se odesílá přes HTTPS do Edge Function `submit-poptejplot-lead` v Supabase projektu PLOTAO. Záznam se ukládá do zabezpečené tabulky `plotao_leads` se zdrojem `poptejplot.cz` a zobrazí se v administraci PLOTAO.cz mezi ostatními poptávkami. Veřejná stránka obsahuje pouze publishable klíč; žádný service-role ani jiný serverový klíč.

Příjem povoluje pouze origin `https://poptejplot.cz`, server znovu kontroluje povinné údaje, potvrzení informování o předání do administrace, velikost požadavku a limit odesílání. Souhlas s předáním kontaktu vybranému dodavateli je samostatný a nepovinný. Bez něj admin poptávku přijme, ale předání dodavateli zůstane zablokované.

GitHub Pages dál obsluhuje statické soubory. PHP složka `api` není pro hlavní formulář potřeba; registrace dodavatelů na stránce `firmy.html` je samostatný formulář.

## Důležité obchodní a právní údaje

Veřejné informace uvádějí jako správce AO Holding s.r.o. a kontaktní e-mail info@poptejplot.cz. IČO ani sídlo zde nedoplňujeme odhadem; ověřte, zda mají být zveřejněny v právních informacích před dalším rozšiřováním provozu.

## Lokální kontrola

Statické stránky lze otevřít lokálním HTTP serverem. PHP endpoint otestujte pouze na PHP hostingu s testovacími adresami. Nikdy neposílejte skutečné osobní údaje do testovacího formuláře.
