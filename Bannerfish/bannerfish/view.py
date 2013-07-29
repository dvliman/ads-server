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

"""Web resources for viewing ads."""

from twisted.web import resource
from twisted.protocols import http

import util


class BannerPublisher(resource.Resource):
    """Embeds, displays and tracks links and clickthroughs for banners.

    Publishes these URLS:

      * '/embed[?section=<section>]' - returns javascript that when rendered embeds a random ad,
        optionally limited to a specific section.

      * '/embed?batch=i/N[&section=<section>' - e.g. '/embed?batch=1/2' and '/embed?batch=2/2',
        used for embedding multiple ads on same page.

      * '/view?name=<id>' - returns image or Flash for ad

      * '/redirect?name=<id>' - redirects to banner ad's destination

      * '/example?name=<id>' - example of how banner will look
    """

    isLeaf = 1

    def __init__(self, service):
        resource.Resource.__init__(self)
        self.service = service
    
    def render(self, request):
        if request.postpath:
            f = getattr(self, "render_%s" % request.postpath[0], None)
            if not f:
                return "No such page."
            return f(request)
        else:
            return "No such page."

    def render_embed(self, request):
        """Return JS for embedding a random ad."""
        name = util.getArg(request, "name")
        section = util.getArg(request, "section")
        batching = util.getArg(request, "batch")
        if name:
            banner = self.service.banners[name]
        else:
            batch = None
            if batching:
                try:
                    batch, numBatches = map(int, batching.split('/'))
                    if batch <= 0 or batch > numBatches:
                        batch = None
                except ValueError:
                    batch = None
            if batch is not None:
                banner = self.service.banners.getRandomBanner(batch - 1, numBatches, section=section)
            else:
                banner = self.service.banners.getRandomBanner(section=section)
        if banner is None:
            return ""

        request.setHeader('content-type', 'text/javascript')
        self._setNoCaching(request)
        if banner.mimeType == "application/x-shockwave-flash":
            return self.embedFlash(banner, request)
        else:
            return self.embedImage(banner, request)

    def _setNoCaching(self, request):
        request.setHeader('expires', http.datetimeToString(0))
        request.setHeader('cache-control', 'no-cache')
        
    def embedImage(self, banner, request):
        """Return JS for embedding a banner that is an image."""
        nolog = (util.getArg(request, "nolog") and "&nolog=1") or ""
        s = """document.write('<a href=\"%s/redirect?name=%s%s\">');\n""" % (request.prePathURL(), banner.getId(), nolog)
        s += """document.write('<img border=\"0\" src=\"%s/view?name=%s%s\" width=\"%s\" height=\"%s\" alt=\"%s\"></a>');""" % (
            request.prePathURL(), banner.getId(), nolog, banner.width, banner.height, banner.title)
        return s
    
    def embedFlash(self, banner, request):
        """Return JS for embedding a banner that is a flash object."""
        nolog = (util.getArg(request, "nolog") and "&nolog=1") or ""
        url = "%s/view?name=%s%s" % (request.prePathURL(), banner.getId(), nolog)
        h, w = banner.height, banner.width
        s = ("document.write('<OBJECT CLASSID=\"clsid:D27CDB6E-AE6D-11cf-96B8-444553540000\""
             "WIDTH=\"%s\" HEIGHT=\"%s\""
             "CODEBASE=\"http://active.macromedia.com/flash5/cabs/swflash.cab#version=5,0,0,0\">"
             '<PARAM NAME="MOVIE" VALUE="%s">'
             '<PARAM NAME="PLAY" VALUE="true">'
             '<PARAM NAME="QUALITY" VALUE="best">'
             '<PARAM NAME="LOOP" VALUE="true">'
             '<EMBED SRC="%s" WIDTH="%s" HEIGHT="%s" PLAY="true" LOOP="true" QUALITY="best" '
             'PLUGINSPAGE="http://www.macromedia.com/shockwave/download/index.cgi?P1_Prod_Version=ShockwaveFlash">' 
             "</EMBED></OBJECT>');") % (w, h, url, url, w, h)
        return s

    def render_view(self, request):
        """Return banner's image or Flash contents."""
        banner = util.getBanner(self.service, request)
        if banner is None:
            return "No such banner."

        if not util.getArg(request, "nolog"):
            banner.addView() # we record view of ad here
        self._setNoCaching(request)
        request.setHeader("content-type", banner.mimeType)
        request.setHeader("content-length", len(banner.file))
        return banner.file

    def render_redirect(self, request):
        """Redirect to banner's destination."""
        banner = util.getBanner(self.service, request)
        if banner is None:
            return "No such banner."

        if not util.getArg(request, "nolog"):
            banner.addClick()
        request.redirect(banner.URL)
        return ""
