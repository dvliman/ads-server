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

"""TAP creation for Bannerfish server."""

# twisted imports
from twisted.python import usage
from twisted.web import server, guard, resource
from twisted.cred import authorizer

# sibling imports
import service, management, view


class Options(usage.Options):
    synopsis = "Usage: mktap bannerfish [options] <storage path>"
    optParameters = [["port", "p", "6080","Port to start the server on."],
                     ["username", "u", "manager", "Username for management interface."],
                     ["password", "s", "password", "Password for management interface."],
                     ["proxyhost", "H", None, "Real hostname of requests (for use with reverse proxies)."],
                     ]
    longdesc = """\
This creates a bannerfish.tap file that can be used by twistd.
"""

    def parseArgs(self, path):
        """<path> is where ad info will be stored."""
        self.opts['path'] = path


def updateApplication(app, config):
    auth = authorizer.DefaultAuthorizer(app)
    svc = service.BannerService(config.opts['path'], "bannerfish", app, auth)
    p = svc.createPerspective(config.opts['username'])
    p.makeIdentity(config.opts['password'])
    if config.opts["proxyhost"]:
        phost = config.opts["proxyhost"]
        pport = 80
    else:
        phost = pport = None
    site = server.Site(svc.buildResource(phost, pport))
    app.listenTCP(int(config.opts['port']), site)
