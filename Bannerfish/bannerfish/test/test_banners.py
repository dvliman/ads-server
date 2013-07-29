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

import tempfile, shutil, os, operator, random

from pyunit import unittest

from bannerfish import banners


class BannersTestCase(unittest.TestCase):

    def setUp(self):
        self.path = tempfile.mktemp()
        os.mkdir(self.path)
        self.banners = banners.BannerCollection(self.path)

    def tearDown(self):
        del self.banners
        shutil.rmtree(self.path)

    def testNoBanners(self):
        self.assertEquals(self.banners.getRandomBanner(), None)

    def testViews(self):
        b = self.banners.createEntity("test", paidViews=10)

        # adding view shouldn't work
        self.assertEquals(b.views, 0)
        b.addView()
        self.assertEquals(b.views, 0)

        b.enabled = 1
        
        # add views
        for i in range(10):
            self.assertEquals(b.views, i)
            b.addView()

    def testBannerActivation(self):
        banners = self.banners
        b = banners.createEntity("test", paidViews=3)

        # banner shouldn't be active yet
        self.assertEquals(banners.getRandomBanner(), None)
        self.assertEquals(b.isViewable(), 0)

        b.enabled = 1

        # banner should be active for 3 views
        for i in range(3):
            self.assertEquals(banners.getRandomBanner(), b)
            self.assertEquals(b.isViewable(), 1)
            self.assertEquals(b.views, i)
            b.addView()

        # banner should no longer be active
        self.assertEquals(banners.getRandomBanner(), None)
        self.assertEquals(b.isViewable(), 0)

        # an additional view should not be logged
        b.addView()
        self.assertEquals(b.views, 3)

    def testClicks(self):
        b = self.banners.createEntity("test", paidViews=20)

        # adding view shouldn't work
        self.assertEquals(b.clicks, 0)
        b.addClick()
        self.assertEquals(b.clicks, 0)

        b.enabled = 1
        
        # add views
        for i in range(10):
            self.assertEquals(b.clicks, i)            
            b.addClick()
        self.assertEquals(b.clicks, 10)
        
        # an additional click should not be logged after views surpass paidViews
        b.views = 20
        b.addClick()
        self.assertEquals(b.clicks, 10)

    def testWeighting(self):
        b1 = self.banners.createEntity("test", paidViews=20000, weight=0.5, enabled=1)
        b2 = self.banners.createEntity("test2", paidViews=20000, weight=1, enabled=1)
        b3 = self.banners.createEntity("test3", paidViews=20000, weight=2, enabled=1)
        hits = {b1: 0.0, b2: 0.0, b3: 0.0}
        for i in range(10000):
            r = self.banners.getRandomBanner()
            hits[r] += 1.0

        # these following tests have a small chance of failing even if the code is
        # correct.
        
        # we should have twice as many b2 as b1, more or less
        self.assert_(abs((hits[b2] / hits[b1]) - 2) < 0.2)
        
        # we should have twice as many b3 as b2, more or less
        self.assert_(abs((hits[b3] / hits[b2]) - 2) < 0.2)

    def testRandomBatching(self):
        # I should probably add non-random tests as well
        for j in range(10):
            banners = []
            for i in range(random.randint(1, 100)):
                banners.append(self.banners.createEntity("%s-%s" % (j, i), weight=random.uniform(0, 5)))
            for i in range(10):
                batches = self.banners._getBatches(banners, random.randint(1, 100))
                self.assertEquals(banners, _reduceLists(batches))

    def testSections(self):
        b = self.banners.createEntity("test", sections=["a", "b"], paidViews=10, enabled=1)

        # check viewability
        self.assertEquals(b.isViewable(), 1)
        self.assertEquals(b.isViewable("a"), 1)
        self.assertEquals(b.isViewable("b"), 1)
        self.assertEquals(b.isViewable("other"), 0)

        # check random selection
        self.assertEquals(self.banners.getRandomBanner(), b)
        self.assertEquals(self.banners.getRandomBanner(section="a"), b)
        self.assertEquals(self.banners.getRandomBanner(section="a"), b)
        self.assertEquals(self.banners.getRandomBanner(section="other"), None)


def _reduceLists(l):
    return reduce(operator.add, l, [])

# XXX Add tests for mime-type detection
