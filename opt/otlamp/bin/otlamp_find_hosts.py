#!/usr/bin/python3

import glob
import re
import os
from argparse import ArgumentParser
from datetime import datetime, timedelta

parser = ArgumentParser(description='Allows to find desired otlamp-vhosts. All otlamp-vhosts will be found by default.')
parser.add_argument("-i", "--instancemask", help="Filters found results by specified instancemask. Default is *.", dest="instancemask", default="*")
parser.add_argument("-v", "--vhostmask", help="Filters found results by specified vhostmask. Default is *.", dest="vhostmask", default="*")
parser.add_argument("-l", "--lifetimedays", help="Filters found results to select hosts with lifetime longer than specified (in days)", dest="lifetimedays")
parser.add_argument("-d", "--deadline", help="Filters found results to select hosts with passed deadline only", dest="deadline", action="store_true")
parser.add_argument("-e", "--blacklist", help="Comma-separated list in [instance]_[vhost] format. Found host will be excluded from the results if it was blacklisted.", dest="blacklist")
parser.add_argument("-w", "--whitelist", help="Comma-separated list in [instance]_[vhost] format. if this argument is specified, found host shold be whitelisted or it will be excluded from results otherwise", dest="whitelist")
parser.add_argument("-f", "--format", help="Format of result line. Supports replacements: {instance}, {vhost}, {datecreate}, {datettl}, {devttl}. Default is \"{instance} {vhost}\"", dest="format", default="{instance} {vhost}")
parser.add_argument("-c", "--command", help="Specified command should be applied to each found vhost instead of printing result line. Supports replacements: {instance}, {vhost}, {datecreate}, {datettl}, {devttl}", dest="command")

args = parser.parse_args()

lifetimedelta = None if args.lifetimedays is None else timedelta(days=int(args.lifetimedays))
blacklist = [] if args.blacklist is None else [x.strip() for x in args.blacklist.split(',')]
whitelist = None if args.whitelist is None else [x.strip() for x in args.whitelist.split(',')]

def parse_vhost_path(filepath):
    regex = r"\/etc\/opt\/otlamp\/([^\/]+)\/vhosts\/([^\/]+)"
    matches = re.search(regex, filepath)
    instance = matches.group(1)
    vhost = matches.group(2)
    return instance, vhost;

def read_date(filepath):
    if os.path.isfile(filepath):
        f = open(filepath)
        timestamp = int(f.readline().rstrip())
        return datetime.fromtimestamp(timestamp)
    return False

def make_replacements(text, replacements):
    for k,v in replacements.items():
        text = text.replace('{'+k+'}', v)
    return text

datenow = datetime.now()
files = glob.glob('/etc/opt/otlamp/'+args.instancemask+'/vhosts/'+args.vhostmask+'/')
for filepath in files:

    instance, vhost = parse_vhost_path(filepath)

    if whitelist is not None and instance+'_'+vhost not in whitelist:
        continue

    datecreate = read_date(filepath+'datecreate')
    datettl = read_date(filepath+'datettl')
    devttl = read_date('/var/opt/otlamp/'+instance+'/'+vhost+'/www/local/devttl')

    if lifetimedelta is not None and (not datecreate or datenow < (datecreate + lifetimedelta)):
        # задан фильтр, отбирающий хосты которые живут дольше указанного количества дней
        # хост живет меньше указанного количества дней
        # или мы не знаем сколько живет хост, так как не удалось прочитать показатель
        # не берём этот хост в выборку
        continue

    if args.deadline:
        # задан фильтр, отбирающий хосты, у которых наступила дата окончания жизни
        if not datettl or datenow < datettl:
            # срок жизни, указываемый через ансибл еще не наступил
            # или мы не знаем наступил ли он, так как не удалось прочитать показатель
            # не берём этот хост в выборку
            continue
        # если мы здесь, значит согласно ансиблу срок смерти наступил
        if devttl and datenow < devttl:
            # имеется срок жизни, указываемый при билде через эклипс
            # и он еще не наступил
            # не берём этот хост в выборку
            # (если не указан, значит разработчики не хотели дополнительно продлевать жизнь этому хосту)
            continue

    if instance+'_'+vhost in blacklist:
        # этот хост передан в списке для исключения из выборки
        # не берем этот хост в выборку
        continue

    replacements = {
        'instance': instance,
        'vhost': vhost,
        'datecreate': '?' if not datecreate else datecreate.strftime("%Y.%m.%d %H:%M:%S"),
        'datettl': '?' if not datettl else datettl.strftime("%Y.%m.%d %H:%M:%S"),
        'devttl': '?' if not devttl else devttl.strftime("%Y.%m.%d %H:%M:%S"),
    }

    if args.command is not None:
        # передан аргумент для применения команды вместо вывода результирующей строки на экран
        os.system(make_replacements(args.command, replacements))
    else:
        # вывод результирующей строки на экран
        print(make_replacements(args.format, replacements))
