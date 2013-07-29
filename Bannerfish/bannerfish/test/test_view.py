"""Test viewing ads."""

from pyunit import unittest
import threading
import urllib
import tempfile
import os
import shutil

from twisted.web import server, static
from twisted.internet import reactor

from bannerfish import service


class ViewTestCase(unittest.TestCase):

    def setUp(self):
        self.path = tempfile.mktemp()
        os.mkdir(self.path)
        self.service = service.BannerService(self.path, "test", None, None)
        self.site = server.Site(self.service.buildResource())

    def tearDown(self):
        del self.service
        del self.site
        shutil.rmtree(self.path)

    def _doView(self):
        """Runs in thread."""
        try:
            self.viewResult = urllib.urlopen("http://localhost:10080/publish/view?name=test")
        except:
            pass
        reactor.callFromThread(reactor.stop)
    
    def testView(self):
        b = self.service.banners.createEntity("test", paidViews=10, file="abc",
                                              mimeType="foo/bar", enabled=1)
        p = reactor.listenTCP(10080, self.site)
        reactor.callLater(0, lambda: threading.Thread(target=self._doView).start())
        reactor.run()

        self.assertEquals(b.views, 1)
        self.assertEquals(self.viewResult.read(), "abc")
        self.assertEquals(self.viewResult.headers['content-type'], "foo/bar")
        del self.viewResult

    def _doClick(self):
        """Runs in thread."""
        try:
            self.clickResult = urllib.urlopen("http://localhost:10080/publish/redirect?name=test")
        except:
            pass
        reactor.callFromThread(reactor.stop)

    def testClick(self):
        b = self.service.banners.createEntity("test", paidViews=10, enabled=1,
                                              URL="http://localhost:10080/test")
        self.site.resource.putChild("test", static.Data("test resource", "text/plain"))
        p = reactor.listenTCP(10080, self.site)
        reactor.callLater(0, lambda: threading.Thread(target=self._doClick).start())
        reactor.run()

        self.assertEquals(b.clicks, 1)
        self.assertEquals(self.clickResult.geturl(), "http://localhost:10080/test")
        self.assertEquals(self.clickResult.read(), "test resource")
        del self.clickResult
