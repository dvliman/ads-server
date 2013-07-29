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

"""Banner classes."""

# system imports
import random, operator

# sibling imports
import storage


class Banner(storage.Entity):
    """A banner."""

    entityAttributes = ["width", "height", "title", "description", "URL",
                        "mimeType", "paidViews", "views", "clicks",
                        "password", "enabled", "file", "weight", "sections"]

    # default values for attributes
    paidViews = 0
    views = 0
    clicks = 0
    enabled = 0
    weight = 1.0
    sections = ()


    def isViewable(self, section=None):
        """Is the banner visible to viewers?"""
        result = self.enabled and (self.paidViews > self.views)
        if section is not None:
            result = result and (section in self.sections)
        return result

    def addClick(self):
        """Register a clickthrough on the banner."""
        if self.isViewable():
            self.clicks = self.clicks + 1

    def addView(self):
        """Register a view of the banner."""
        if self.isViewable():
            self.views = self.views + 1

    # should I refactor this into a decent factory method?
    def update(self, kwargs):
        """Update with new values."""
        for k, v in kwargs.items():
            if k not in self.entityAttributes: continue
            if k == "file":
                if v:
                    # guess mime-type
                    if v.startswith('FWS'): self.mimeType = "application/x-shockwave-flash"
                    elif v.startswith("GIF"): self.mimeType = "image/gif"
                    elif v.startswith('\xff\xd8\xff\xe0\x00\x10JFIF'): self.mimeType = "image/jpeg"
                    elif v.startswith('\x89PNG'): self.mimeType = "image/png"
                    else: self.mimeType = "text/plain"
                elif hasattr(self, "file") and self.file:
                    # if we have an image set already, don't overwrite it with non-existent input
                    continue
            setattr(self, k, v)


class BannerCollection(storage.Collection):
    """A collection of banner ads."""

    entityClass = Banner

    def _chooseRandomBanner(self, banners):
        """Return a random banner, based on their weight.

        For example, a banner with weight 2 is twice as likely to be
        returned as a banner with weight 1, which in turn is twice
        as likely to be returned as a banner with weight 0.5.
        """
        bannerWeights = [b.weight for b in banners]
        weightSum = reduce(operator.add, bannerWeights, 0)
        choice = random.uniform(0, weightSum)
        accumulatedWeight = 0
        for i in range(len(banners)):
            accumulatedWeight += bannerWeights[i]
            if choice < accumulatedWeight:
                break
        return banners[i]

    def _getBatches(self, banners, N):
        """Divide banners list into N batches.

        We will attempt to have each batch have the same weight as the
        others, more or less.
        """
        banners.sort()
        batches = []
        bannerWeights = [b.weight for b in banners]
        batchWeight = reduce(operator.add, bannerWeights, 0) / float(N)
        j = 0
        for i in range(N):
            batch = []
            batches.append(batch)
            accumulatedWeight = 0
            while accumulatedWeight < batchWeight and  j < len(banners) - (N - i - 1):
                batch.append(banners[j])
                accumulatedWeight += bannerWeights[j]
                j += 1
        return batches

    def getRandomBanner(self, batch=None, numBatches=None, section=None):
        """Return a random banner that is viewable.

        Returns None if there are none. If batch and numBatches arguments
        are given (lets say 0 and 3), the banners will chosen batch 0
        out of 3 possible batches. This is used to allow multiple banners
        on same page while still making sure no banner appears twice on
        the same page.
        """
        viewableBanners = filter(lambda b: b.isViewable(section), self.values())
        if numBatches:
            viewableBanners = self._getBatches(viewableBanners, numBatches)[batch]
        if viewableBanners:
            return self._chooseRandomBanner(viewableBanners)
        else:
            return None
