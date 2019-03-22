#/bin/sh
echo '' > /tmp/reptile/reptili_project/log.txt
nohup php /tmp/reptile/reptili_project/run.php 1>/tmp/reptile/reptili_project/log.txt &

echo "爬虫启动成功，请用 tail -f ./log.txt 查看进度 命令提示:  ps -ef | grep php   查出脚本开启的run.php pid  kill"
