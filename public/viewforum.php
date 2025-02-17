<?php

use RA\Permissions;

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../lib/bootstrap.php';

RA_ReadCookieCredentials($user, $points, $truePoints, $unreadMessageCount, $permissions);

$requestedForumID = requestInputSanitized('f', null, 'integer');
$offset = requestInputSanitized('o', 0, 'integer');
$count = requestInputSanitized('c', 25, 'integer');

$numUnofficialLinks = 0;
if ($permissions >= Permissions::Admin) {
    $unofficialLinks = getUnauthorisedForumLinks();
    $numUnofficialLinks = is_countable($unofficialLinks) ? count($unofficialLinks) : 0;
}

$numTotalTopics = 0;

if ($requestedForumID == 0) {
    if ($permissions >= Permissions::Admin) {
        // Continue
        $viewingUnauthorisedForumLinks = true;
    } else {
        header("location: " . getenv('APP_URL') . "/forum.php?e=unknownforum");
        exit;
    }

    $thisForumID = 0;
    $thisForumTitle = "Unauthorised Links";
    $thisForumDescription = "Unauthorised Links";
    $thisCategoryID = 0;
    $thisCategoryName = "Unauthorised Links";

    $topicList = getUnauthorisedForumLinks();

    $requestedForum = "Unauthorised Links";
} else {
    if (getForumDetails($requestedForumID, $forumDataOut) == false) {
        header("location: " . getenv('APP_URL') . "/forum.php?e=unknownforum2");
        exit;
    }

    $thisForumID = $forumDataOut['ID'];
    $thisForumTitle = $forumDataOut['ForumTitle'];
    $thisForumDescription = $forumDataOut['ForumDescription'];
    $thisCategoryID = $forumDataOut['CategoryID'];
    $thisCategoryName = $forumDataOut['CategoryName'];

    $topicList = getForumTopics($requestedForumID, $offset, $count, $permissions, $numTotalTopics);

    $requestedForum = $thisForumTitle;
}

sanitize_outputs(
    $requestedForum,
    $thisForumTitle,
    $thisForumDescription,
    $thisCategoryName,
);

$errorCode = requestInputSanitized('e');
$mobileBrowser = IsMobileBrowser();

RenderHtmlStart();
RenderHtmlHead("View forum: $thisForumTitle");
?>
<body>
<?php RenderTitleBar($user, $points, $truePoints, $unreadMessageCount, $errorCode, $permissions); ?>
<?php RenderToolbar($user, $permissions); ?>
<div id="mainpage">
    <div id="leftcontainer">
        <div id="forums">
            <?php
            echo "<div class='navpath'>";
            echo "<a href='/forum.php'>Forum Index</a>";
            echo " &raquo; <a href='/forum.php?c=$thisCategoryID'>$thisCategoryName</a>";
            echo " &raquo; <b>$requestedForum</b></a>";
            echo "</div>";

            if ($numUnofficialLinks > 0) {
                echo "<br><a href='/viewforum.php?f=0'><b>Administrator Notice:</b> $numUnofficialLinks unofficial posts need authorising: please verify them!</a><br>";
            }

            // echo "<h2 class='longheader'><a href='/forum.php?c=$nextCategoryID'>$nextCategory</a></h2>";
            echo "<h2>$requestedForum</h2>";
            echo "$thisForumDescription<br>";

            if ($permissions >= Permissions::Registered) {
                echo "<a href='createtopic.php?f=$thisForumID'><div class='rightlink'>[Create New Topic]</div></a>";
            }

            /* Forum pagination */
            $forumPagination = "";

            if ($numTotalTopics > $count) {
                $forumPagination .= "<tr>";

                $forumPagination .= "<td class='forumpagetabs' colspan='2'>";
                $forumPagination .= "<div class='forumpagetabs'>";

                $forumPagination .= "Page:&nbsp;";
                $pageOffset = ($offset / $count);
                $numPages = ceil($numTotalTopics / $count);

                if ($pageOffset > 0) {
                    $prevOffs = $offset - $count;
                    $forumPagination .= "<a class='forumpagetab' href='/viewforum.php?f=$requestedForumID&amp;o=$prevOffs'>&lt;</a> ";
                }

                for ($i = 0; $i < $numPages; $i++) {
                    $nextOffs = $i * $count;
                    $pageNum = $i + 1;

                    if ($nextOffs == $offset) {
                        $forumPagination .= "<span class='forumpagetab current'>$pageNum</span> ";
                    } else {
                        $forumPagination .= "<a class='forumpagetab' href='/viewforum.php?f=$requestedForumID&amp;o=$nextOffs'>$pageNum</a> ";
                    }
                }

                if ($offset + $count < $numTotalTopics) {
                    $nextOffs = $offset + $count;
                    $forumPagination .= "<a class='forumpagetab' href='/viewforum.php?f=$requestedForumID&amp;o=$nextOffs'>&gt;</a> ";
                }

                $forumPagination .= "</div>";
                $forumPagination .= "</td>";
                $forumPagination .= "</tr>";
            }

            echo $forumPagination;

            echo "<table><tbody>";
            echo "<tr class='forumsheader'>";
            echo "<th></th>";
            echo "<th class='fullwidth'>Topics</th>";
            echo "<th>Author</th>";
            echo "<th>Replies</th>";
            // echo "<th>Views</th>";
            echo "<th class='text-nowrap'>Last post</th>";
            echo "</tr>";

            $topicCount = is_countable($topicList) ? count($topicList) : 0;

            $topicIter = 0;

            // Output all topics, and offer 'prev/next page'
            foreach ($topicList as $topicData) {
                // Output one forum, then loop
                $nextTopicID = $topicData['ForumTopicID'];
                $nextTopicTitle = $topicData['TopicTitle'];
                $nextTopicPreview = $topicData['TopicPreview'];
                $nextTopicAuthor = $topicData['Author'];
                $nextTopicAuthorID = $topicData['AuthorID'];
                $nextTopicPostedDate = $topicData['ForumTopicPostedDate'];
                $nextTopicLastCommentID = $topicData['LatestCommentID'];
                $nextTopicLastCommentAuthor = $topicData['LatestCommentAuthor'];
                $nextTopicLastCommentAuthorID = $topicData['LatestCommentAuthorID'];
                $nextTopicLastCommentPostedDate = $topicData['LatestCommentPostedDate'];
                $nextTopicNumReplies = $topicData['NumTopicReplies'];

                sanitize_outputs(
                    $nextTopicTitle,
                    $nextTopicPreview,
                    $nextTopicAuthor,
                    $nextTopicLastCommentAuthor,
                );

                if ($nextTopicPostedDate !== null) {
                    $nextTopicPostedNiceDate = getNiceDate(strtotime($nextTopicPostedDate));
                } else {
                    $nextTopicPostedNiceDate = "None";
                }

                if ($nextTopicLastCommentPostedDate !== null) {
                    $nextTopicLastCommentPostedNiceDate = getNiceDate(strtotime($nextTopicLastCommentPostedDate));
                } else {
                    $nextTopicLastCommentPostedNiceDate = "None";
                }

                echo "<tr>";

                echo "<td class='unreadicon p-1'><img src='" . getenv('ASSET_URL') . "/Images/ForumTopicUnread32.gif' width='20' height='20' title='No unread posts' alt='No unread posts'></img></td>";
                echo "<td class='topictitle'><a alt='Posted $nextTopicPostedNiceDate' title='Posted on $nextTopicPostedNiceDate' href='/viewtopic.php?t=$nextTopicID'>$nextTopicTitle</a><br><div id='topicpreview'>$nextTopicPreview...</div></td>";
                echo "<td class='author'>";
                echo GetUserAndTooltipDiv($nextTopicAuthor, $mobileBrowser);
                echo "</td>";
                // echo "<td class='author'><div class='author'><a href='/user/$nextTopicAuthor'>$nextTopicAuthor</a></div></td>";
                echo "<td class='replies'>$nextTopicNumReplies</td>";
                // echo "<td class='views'>$nextForumNumViews</td>";
                echo "<td class='lastpost'>";
                echo "<div class='lastpost'>";
                echo "<span class='smalldate'>$nextTopicLastCommentPostedNiceDate</span><br>";
                echo GetUserAndTooltipDiv($nextTopicLastCommentAuthor, $mobileBrowser);
                // echo "<a href='/user/$nextTopicLastCommentAuthor'>$nextTopicLastCommentAuthor</a>";
                echo " <a href='viewtopic.php?t=$nextTopicID&amp;c=$nextTopicLastCommentID#$nextTopicLastCommentID' title='View latest post' alt='View latest post'>[View]</a>";
                echo "</div>";
                echo "</td>";
                echo "</tr>";
            }

            echo "</tbody></table>";

            echo $forumPagination;

            echo "<br>";

            if ($permissions >= Permissions::Registered) {
                echo "<div class='rightlink'><a href='createtopic.php?f=$thisForumID'>[Create New Topic]</a></div>";
            } else {
                echo "<div class='rightlink'><span class='hoverable' title='Unregistered: please check your email registration link!'>[Create New Topic]</span></div>";
            }

            echo "<br>";

            ?>
        </div>
    </div>
    <div id="rightcontainer">
        <?php
        RenderRecentForumPostsComponent($permissions, 8);
        ?>
    </div>
</div>
<?php RenderFooter(); ?>
</body>
<?php RenderHtmlEnd(); ?>
