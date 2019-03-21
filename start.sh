#/bin/sh

nohup php ./run.php 1>./log.txt &

echo "爬虫启动成功，请用 tail -f ./log.txt 查看进度"
