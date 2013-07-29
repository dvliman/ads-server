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

import os, tempfile
from pyunit import unittest

from bannerfish import storage


class TestEntity(storage.Entity):

    entityAttributes = ["foo", "x", "y"]

    foo = 1


class TestCollection(storage.Collection):

    entityClass = TestEntity


class StorageTestCase(unittest.TestCase):

    def testClassAttributes(self):
        path = tempfile.mktemp()
        os.mkdir(path)
        c = TestCollection(path)

        e = c.createEntity("test")
        self.assertEquals(e.foo, 1)
        e.foo = "b"
        self.assertEquals(e.foo, "b")
        self.assertEquals(e._dbm["foo"], "b")

        del e, c
        c = TestCollection(path)
        e = c["test"]
        self.assertEquals(e.foo, "b")
    
    def testStorage(self):
        path = tempfile.mktemp()
        os.mkdir(path)
        c = TestCollection(path)
        
        e = c.createEntity("test", foo=1)

        self.assertEquals(e.foo, 1)
        self.assertEquals(c.keys(), ["test"])
        self.assertEquals(c.values(), [e])

        e.x = "foo"
        self.assertEquals(e.x, "foo")

        del c
        del e

        c = TestCollection(path)
        e = c["test"]
        self.assertEquals(c.keys(), ["test"])
        self.assertEquals(c.values(), [e])
        try:
            e.z = 3
        except ValueError:
            pass
        else:
            raise RuntimeError, "shouldn't be able to do that"

