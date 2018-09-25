<!doctype html>
<html>
    <head>
        <!-- Google Tag Manager -->
        <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
        new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
        j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
        'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
        })(window,document,'script','dataLayer','GTM-TX9WD6B');</script>
        <!-- End Google Tag Manager -->
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <title>Slovnaft Bajk - štatistiky systému bike sharing</title>
        <meta name="description" content="Štatistiky systému Slovnaft Bajk - priemerná vyťaženosť bicyklov za 24 hodín, zmeny počtu staníc a bicyklov v čase. Pozri aj ďalšie prehľady!">
        <meta property="og:url" content="https://slovnaftbajk.cyklokoalicia.sk/public/" />
        <meta property="og:site_name" content="Cyklokoalícia" />
        <meta property="og:image" content="{{secure_url('ogimage', ['time'=>time()])}}" />
        <meta property="og:updated_time" content="{{time()}}" />
        <meta name="twitter:card" content="summary_large_image" />
        <meta name="twitter:title" content="Štatistiky systému Slovnaft Bajk" />
        <meta name="twitter:description" content="Štatistiky systému Slovnaft Bajk - priemerná vyťaženosť bicyklov za 24 hodín, zmeny počtu staníc a bicyklov v čase. Pozri aj ďalšie prehľady!" />
        <meta name="twitter:image" content="{{secure_url('ogimage', ['time'=>time()])}}" />
        <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
        <link href="{{asset('css/open-iconic-bootstrap.min.css')}}" rel="stylesheet">
    </head>
    <body>
        <!-- Google Tag Manager (noscript) -->
        <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-TX9WD6B"
        height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
        <!-- End Google Tag Manager (noscript) -->
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <h1>Štatistiky systému Slovnaft Bajk</h1>
                    <p>Mestský bike sharing Slovnaft Bajk, Bratislava</p>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <h2 class="display-1 text-center bg-success text-white">{{$daily_summary[0]->bicycles}}
                    </h2>
                    <h2 class="display-4 text-center">bicyklov dnes v meste
                        @if ($daily_change<0)
                            <span class="oi oi-arrow-bottom" aria-hidden="true"></span>
                        @elseif ($daily_change>0)
                            <span class="oi oi-arrow-top" aria-hidden="true"></span>
                        @endif
                        <small>{{sprintf('%+d',$daily_change)}}%</small></h2>
                    <hr>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <h2>Priemerný počet využitých bicyklov za posledných 24 hodín</h2>
                    <p>Priemerný počet bicyklov, ktoré boli v "obehu" a neboli dostupné v staniciach. Počet požičaní bicyklov môže byť vyšší.<br>
                    <small><strong>Ako sme číslo zistili?</strong> Od celkového dostupného počtu bicyklov v daný deň odpočítame priemerný počet dostupných bicyklov v danú hodinu.</small></p>
                    <table class="table table-striped table-bordered">
                        <thead class="thead-dark">
                            <tr>
                                <th scope="col">Hodina</th>
                                <th scope="col">Počet využitých bicyklov</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach ($free_bicycles as $hour=>$bicycles)
                            <tr>
                                <td>{{$hour}}</td>
                                <td>{{round($total_bicycles-$bicycles->free)}}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                    <hr>
                    <h2>Priemerný počet dostupných bicyklov v stanici</h2>
                    <p><small><strong>Ako sme číslo zistili?</strong> Vypočítali sme priemer počtu bicyklov dostupných v danej stanici od začiatku fungovania systému.</small></p>
                    <table class="table table-striped table-bordered">
                        <thead class="thead-dark">
                            <tr>
                              <th scope="col">Stanica</th>
                              <th scope="col">Priemerný počet dostupných bicyklov</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($stations as $station_id=>$name)
                                @if(isset($average_bicycles[$station_id]) and $average_bicycles[$station_id]->free>0)
                                    <tr>
                                      <td>{{$stations[$station_id]->name}}</td>
                                      <td>{{number_format($average_bicycles[$station_id]->free,2,',','.')}}</td>
                                    </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                    <h2>Počet staníc a bicyklov v systéme za posledný týždeň</h2>
                    <p><small><strong>Ako sme číslo zistili?</strong> V čase nulovej vyťaženosti (o 4:00) ráno zistíme počet dostupných bicyklov. Počet staníc zisťujeme priebežne, preto v ňom môžu byť zachytené aj testovacie, neverejné stanice. Tie však dostatočne z dát odstránime.</small></p>
                    <table class="table table-striped table-bordered">
                        <thead class="thead-dark">
                            <tr>
                              <th scope="col">Dátum</th>
                              <th scope="col">Počet staníc</th>
                              <th scope="col">Počet bicyklov</th>
                              <th scope="col">Vyťaženosť systému</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($daily_summary as $daily)
                                <tr>
                                  <td>{{$daily->day}}</td>
                                  <td>{{$daily->stations}}</td>
                                  <td>{{$daily->bicycles}}</td>
                                  <td>{{round($daily->utilization*100,2)}}%</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div class="card">
                        <div class="card-header">(c) <a href="https://cyklokoalicia.sk/">Cyklokoalícia</a> 2018+, dáta sú z webu slovnaftbajk.sk. <img alt="Creative Commons License" style="border-width:0" src="https://i.creativecommons.org/l/by/4.0/88x31.png" /> Creative Commons Attribution 4.0 International License</div>
                        <div class="card-body">
                            <p class="card-text">Dáta sú získavané a publikované vo verejnom záujme, systém Slovnaft Bajk je spolufinancovaný z daní obyvateľov Bratislavy vo výške pol milión Eur. Údaje máme v databáze od 9.9.2018.</p>
                            <p class="card-text">Chyby, nepresnosti, komentáre? Napíšte nám na: info@cyklokoalicia.sk</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>
