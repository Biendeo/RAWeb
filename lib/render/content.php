<?php

function RenderTutorialComponent()
{
    echo "<div class='component tutorial' >";
    echo "<h3>How Do I Play?</h3>";
    echo "<p>";
    echo "<a href='/'>RetroAchievements</a> provides emulators for your PC where you can earn achievements while you play games!<br><br>";
    echo "<i>\"...like Xbox Live&trade; for emulation!\"</i><br><br>";
    echo "<a href='/download.php'>Download an emulator</a> for your chosen console, <a href='//www.retrode.com/'>find</a> some <a href='//www.lmgtfy.com/?q=download+mega+drive+roms'>ROMs</a> and join the fun!";
    echo "</p>";
    echo "</div>";
}

function RenderWelcomeComponent()
{
    echo "
    <div class='component welcome'>
        <h2>Welcome!</h2>
        <div id='Welcome'>
            <p>
            Were you the greatest in your day at Mega Drive or SNES games? Wanna prove it? Use our modified emulators and you will be awarded achievements as you play! Your progress will be tracked so you can compete with your friends to complete all your favourite classics to 100%: we provide the emulators for your Windows-based PC, all you need are the roms!<br>
            <a href='/game/1'>Click here for an example:</a> which of these do you think you can get?
            </p>
        <br>
            <p style='clear:both; text-align:center'>
            <a href='/download.php'><b>&gt;&gt;Download an emulator here!&lt;&lt;</b></a><br>
            </p>
        </div>
    </div>";
}

function RenderDocsComponent()
{
    echo "
      <div class='component' style='text-align: center'>
        <div id='docsbox' class='infobox'>
          <div>
            <a href='https://docs.retroachievements.org/' target='_blank' rel='noopener'>📘 Documentation</a> & <a href='https://docs.retroachievements.org/FAQ/' target='_blank' rel='noopener'>FAQ</a>.
          </div>
        </div>
      </div>";
}

function RenderCurrentlyOnlineComponent()
{
    echo "<div class='component'>";
    echo "<h3>Currently Online</h3>";
    echo "<div id='playersonlinebox' class='infobox'>";

    $playersArray = getCurrentlyOnlinePlayers();

    $numPlayers = is_countable($playersArray) ? count($playersArray) : 0;
    echo "<div>There are currently <strong>$numPlayers</strong> players online.</div>";

    echo "</div>";

    echo "<div class='rightfloat lastupdatedtext'><small><span id='playersonline-update'></span></small></div>";
    echo "</div>";
}

function RenderActivePlayersComponent()
{
    echo <<<HTML
        <div id='active-players-component' class='component activeplayerscomponent'>
            <h3>Active Players</h3>
            <div id='playersNotice' style='margin-bottom: 7px'>
                <span style='margin-bottom: 5px; display: inline-block;'>
                    There are <strong data-bind="text: numberOfFilteredPlayers"></strong> <span data-bind='visible: usersAreFiltered'>filtered</span> active players<span data-bind='visible: usersAreFiltered'> (out of <strong data-bind='text: numberOfPlayersActive'></strong> total)</span>.
                </span>
                <a class='rightfloat' id='active-players-menu-button' href='#!' data-bind='click: OnActivePlayersMenuButtonClick, css: { menuOpen: shouldMenuBeVisible }'></a>
                <div id='active-player-menu' data-bind='visible: shouldMenuBeVisible'>
                    <div>
                        <input type='text' style='width: 100%;' placeholder='Filter by player, game, console, or Rich Presence...' data-bind='value: playerFilterText, valueUpdate: "input"' />
                    </div>
                    <div id='active-players-filter-options'>
                        <label><input type='checkbox' data-bind='checked: rememberFiltersValue' /> Remember My Filter</label>
                    </div>
                </div>
            </div>
            <div id='activeplayersbox' style='min-height: 54px'>
                <table class='smalltable' data-bind='hidden: isLoading'>
                    <thead>
                        <th>User</th>
                        <th>Game</th>
                        <th>Currently...</th>
                    </thead>
                    <tbody>
                        <!-- ko foreach: filteredPlayers -->
                        <tr>
                            <td data-bind='html: playerHtml'></td>
                            <td data-bind='html: gameHtml'></td>
                            <td data-bind='text: richPresence'></td>
                        </tr>
                        <!-- /ko -->

                        <tr data-bind='visible: filteredPlayers().length === 0'>
                            <td colspan='3'>No players could be found.</td>
                        </tr>
                    </tbody>
                </table>
                <span data-bind='visible: isLoading'>Loading players...</span>
                <span data-bind='visible: hasError'>An error has occurred while loading players.</span>
            </div>
            <div class='rightfloat lastupdatedtext'>
                <small id='activeplayers-update' data-bind='text: lastUpdateRender'></small>
            </div>
        </div>
    HTML;

    if (getenv('APP_ENV') === 'local') {
        echo '<script type="text/javascript" src="/js/activePlayersBootstrap.js?' . random_int(0, mt_getrandmax()) . '"></script>';
    } else {
        echo '<script type="text/javascript" src="/js/activePlayersBootstrap-' . VERSION . '.js"></script>';
    }
}

function RenderAOTWComponent($achID, $forumTopicID)
{
    $achData = [];
    if (!getAchievementMetadata($achID, $achData)) {
        return;
    }

    echo "<div class='component aotwcomponent' >";
    echo "<h3>Achievement of the Week</h3>";

    /**
     * id attribute used for scraping. NOTE: this will be deprecated. Use API_GetAchievementOfTheWeek instead
     */
    echo "<div id='aotwbox' style='text-align:center;'>";

    $gameID = $achData['GameID'];
    $gameTitle = $achData['GameTitle'];
    $gameIcon = $achData['GameIcon'];
    $consoleName = $achData['ConsoleName'];

    $achID = $achData['AchievementID'];
    $achTitle = $achData['AchievementTitle'];
    $achDesc = $achData['Description'];
    $achBadgeName = $achData['BadgeName'];
    $achPoints = $achData['Points'];
    $achTruePoints = $achData['TrueRatio'];

    sanitize_outputs(
        $gameTitle,
        $consoleName,
        $achTitle,
        $achDesc,
    );

    echo "Achievement: ";
    echo GetAchievementAndTooltipDiv($achID, $achTitle, $achDesc, $achPoints, $gameTitle, $achBadgeName, true);
    echo "<br>";

    echo "on Game: ";
    echo GetGameAndTooltipDiv($gameID, $gameTitle, $gameIcon, $consoleName, false, 32);
    echo "<br>";

    echo "<span class='clickablebutton'><a href='/viewtopic.php?t=$forumTopicID'>Join this tournament!</a></span>";

    echo "</div>";

    echo "</div>";
}
