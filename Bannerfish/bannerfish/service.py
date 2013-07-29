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

"""Service that serves banners."""

# system imports
import os

# twisted imports
from twisted.cred.service import Service
from twisted.web import resource, server, guard, widgets
from twisted.persisted import styles

# sibling imports
from banners import BannerCollection
import view, management, report, util, storage


class Config(storage.Entity):
    """Configuration for banner service."""

    entityAttributes = ["sections"]

    # default values
    sections = ()

    def __init__(self, basepath):
        path = os.path.join(basepath, "-config")
        storage.Entity.__init__(self, "config", path)


class BannerService(styles.Versioned, Service):
    """A service for serving banner ads."""

    persistenceVersion = 2
    
    def upgradeToVersion2(self):
        self.config = Config(self.banners.path)
    
    def __init__(self, path, serviceName, serviceParent, authorizer):
        Service.__init__(self, serviceName, serviceParent, authorizer=authorizer)
        self.banners = BannerCollection(path)
        self.config = Config(path)

    def buildResource(self, host=None, port=None, ssl=None):
        """Return a resource that can be published using twisted.web."""
        if host:
            r = util.ProxiedResource(host, port, ssl=ssl)
        else:
            r = resource.Resource()
        r.putChild("report", widgets.WidgetPage(report.ReportWidget(self)))
        r.putChild("publish", view.BannerPublisher(self))
        r.putChild("manage", guard.ResourceGuard(management.Management(self), self))
        return r
