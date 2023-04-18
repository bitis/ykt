#!/usr/bin/env php
<?php
require __DIR__ . '/vendor/autoload.php';

use App\Ykt;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\SingleCommandApplication;

function now() {
    return date('Y-m-s H:i:s');
}

(new SingleCommandApplication())
    ->setName('1kt')
    ->setVersion('1.0.0')
    ->addArgument('signUpUserId', InputArgument::REQUIRED)
    ->addArgument('onlineClassId', InputArgument::REQUIRED)
    ->addArgument('pcToken', InputArgument::REQUIRED)
    ->addArgument('userId', InputArgument::REQUIRED)
    ->setCode(function (InputInterface $input, OutputInterface $output) {

        $pcToken = $input->getArgument('pcToken');
        $userId = $input->getArgument('userId');
        $signUpUserId = $input->getArgument('signUpUserId');
        $onlineClassId = $input->getArgument('onlineClassId');

        $ekt = new Ykt($pcToken, $userId, $signUpUserId);

        $columnList = $ekt->listColumn($onlineClassId);

        foreach ($columnList as $column) {
            $onlineColumnId = $column['onlineColumnId'];
            $output->writeln(now() . "\t" . $column['onlineColumnName'] . ($column['finishedStatus'] ? "\t已完成" : "\t未学完"));

            if ($column['finishedStatus'] == 1) continue;

            $dataArticle = $ekt->listDataArticle($onlineClassId, $onlineColumnId);

            foreach ($dataArticle as $article) {
                $playStatus = ($article['totalTime'] == $article['playTime']) ? "\t已学完" : "\t"
                    . round($article['playTime'] / $article['totalTime'] / 100) . "%";
                $answerStatus = $article['questionFinishCount'] . '/' . $article['questionTotalCount'];

                $dataId = $article['onlineDataId'];

                $hasQuestion = ($article['questionFinishCount'] - $article['questionTotalCount']);

                $questionList = $ekt->onlineVideo($onlineClassId, $onlineColumnId, $dataId, $article['isOptionalVideo']);

                $output->writeln(now() . "\t" . $article['articleTitle']
                    . "(时长：" . $article['totalTime'] . "s)" . "\t" . "播放: " . $playStatus . "\t 答题：" . $answerStatus);

                if (($article['totalTime'] == $article['playTime'])
                    and $article['questionTotalCount'] == $article['questionFinishCount']) continue;

                // 开始学习；
                $videoId = $article['onlineDataId'];
                $totalTime = $article['totalTime'];
                $videoPlayTime = 1;

                $bar = new ProgressBar($output, $totalTime);
                $bar->start();

                do {
                    if ($hasQuestion) {
                        foreach ($questionList as $question) {
                            if ($question['anchorPopupTime'] == $videoPlayTime) {
                                $answers = '';

                                foreach ($question['optionList'] as $option) {
                                    if ($option['optionIsTrue'] == 1) $answers .= $option['optionNumber'];
                                }
                                $ekt->onlineAnswer($onlineClassId, $onlineColumnId, $dataId, $question['examQuestionId'], $question['anchorId'], $answers);
                            }
                        }
                    }

                    if ($videoPlayTime % 10 == 0 or $article['totalTime'] == $videoPlayTime) {
                        if ($videoPlayTime == $article['totalTime']) {
                            $ekt->monitorProcess($onlineClassId, $onlineColumnId, $videoId, $videoPlayTime, 5);
                            $ekt->monitorProcess($onlineClassId, $onlineColumnId, $videoId, $videoPlayTime, 0);
                        } else {
                            $ekt->monitorProcess($onlineClassId, $onlineColumnId, $videoId, $videoPlayTime, 10);
                        }
                    }

                    $videoPlayTime++;
                    sleep(1);
                    $bar->advance();
                } while ($totalTime--);
                $bar->finish();
                $bar->clear();
            }
        }
        return true;
    })
    ->run();