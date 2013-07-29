# = Bannerfish =
# Copyright (c) 2002 Itamar Shtull-Trauring, all rights reserved.
# 
# This application is free software; you can redistribute it and/or
# modify it under the terms of version 2 of the GNU General Public
# License as published by the Free Software Foundation.
# 
# This application is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
# General Public License for more details.
# 
# You should have received a copy of the GNU General Public License
# along with this application; if not, write to the Free Software
# Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

"""Reporting interface."""

# system imports
import posixpath, urlparse

# twisted imports
from twisted.web import widgets

# sibling imports
import util


class ReportWidget(widgets.StreamWidget):
    """Displays report on banner status."""

    title = "Banner Report"
    
    def __init__(self, service):
        self.service = service
    
    def stream(self, write, request):
        banner = util.getBanner(self.service, request)
        password = util.getArg(request, "password")
        if banner is None or banner.password != password:
            write(('<form action="%s">' % request.prePathURL()) +
                  'Banner ID: <input name="name" /><br />'
                  'Password: <input type="password" name="password" /><br />'
                  '<input type="submit" /></form>')
            return
        write("<h2>Banner title: %s</h2>\n" % banner.title)
        write("<p>Paid views: %s</p>\n" % banner.paidViews)
        write("<p>Views: %s</p>\n" % banner.views)
        write("<p>Clicks: %s</p>\n" % banner.clicks)
        write("<p>Currently running: %s</p>\n" % (banner.isViewable() and "Yes") or "No")

        parts = list(urlparse.urlsplit(request.prePathURL()))
        parts[2] = posixpath.dirname(parts[2])
        baseURL = urlparse.urlunsplit(parts)
        write('<h2>Preview</h2>\n')
        write('<script src="%spublish/embed?name=%s&nolog=1"></script>'
              % (baseURL, util.getArg(request, "name")))


