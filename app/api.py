#!/usr/bin/env python3
# coding=utf-8

"""
@version:0.1
@author: ysicing
@file: api.py
@time: 2017/2/28 下午11:40
"""

from flask_frozen import Freezer
from flask_flatpages import FlatPages
from datetime import datetime,date
from flask import current_app as  app
import os
import re
import time
import sys

try:
    reload(sys)
    sys.setdefaultencoding('utf-8')
except:
    pass

FLATPAGES_ROOT = 'content'
FLATPAGES_EXTENSION = '.md'
FLATPAGES_ENCODING = 'utf8'
FLATPAGES_AUTO_RELOAD = True
POST_DIR = 'posts'
NOTES_DIR = 'notes'
POST_PER_PAGE= 10
app.config.from_object(__name__)
flatpages = FlatPages(app)
freezer = Freezer(app)

def get_list():
    try:
        posts_list = [p for p in flatpages if p.path.startwith(POST_DIR)]
    except:
        posts_list = [p for p in flatpages if p.path]
        #run this code
    try:
        posts_list.sort(key=lambda item: item['date'],reverse=True)
    except:
        posts_list = sorted(posts_list,reverse=True, key=lambda p:p['date'])
    return posts_list

#app.add_template_global(get_posts_list,'posts_lists')

