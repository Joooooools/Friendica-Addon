<!DOCTYPE html>
<html lang="{{$lang}}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{$username}} — Personal Archive</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="theme-{{$theme}}">
    <header class="main-header">
        <div class="header-container">
            <a href="index.html" class="header-brand">
                <img src="{{$avatar_url}}" class="avatar" alt="Avatar">
                <div class="header-text">
                    <h1>{{$username}}</h1>
                    <p class="subtitle">{{$full_handle}}</p>
                </div>
            </a>
            <div class="search-wrap">
                <input type="text" id="searchInput" placeholder="{{$search_placeholder}}">
            </div>
        </div>
    </header>

    <div class="container">
        <aside id="sidebar">
            <nav id="nav-list"></nav>
        </aside>
        <main id="content">
            <div id="post-list">
                <div class="welcome">
                    <h2>{{$welcome_h}}</h2>
                    <p>{{$welcome_p}}</p>
                </div>
            </div>
        </main>
    </div>

    <script>
        window.FRIENDICA_EXPORT_DATA = {};
        window.FRIENDICA_L10N = {{$l10n_json}};
    </script>
    <script src="data/manifest.js"></script>
    <script src="data/search.js"></script>
    <script src="viewer.js"></script>
</body>
</html>
