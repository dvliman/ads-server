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

"""Utilities for working with web interface."""

# twisted imports
from twisted.web import resource


def getArg(request, name):
    """Get arg from HTTP request, or None."""
    name = request.args.get(name)
    if not name:
        return None
    return name[0]


def getBanner(service, request):
    """Return banner object based on HTTP request.

    Parses HTTP GET or POST arg 'name=xxx', and returns banner with
    that id, or None if it doesn't exist or there was no 'name' in request.
    """
    name = getArg(request, "name")
    if name is None:
        return None
    try:
         return service.banners[name]
    except KeyError:
        return None


class ProxiedResource(resource.Resource):
    """Sets the host/port to one different than what we're listening on.

    This is required for working with reverse HTTP proxies.
    """

    def __init__(self, host, port, ssl=0):
        resource.Resource.__init__(self)
        self.host = host
        self.port = port
        self.ssl = ssl

    def getChildWithDefault(self, path, request):
        request.setHost(self.host, self.port, ssl=self.ssl)
        return resource.Resource.getChildWithDefault(self, path, request)
