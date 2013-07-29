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

"""Very simple persistence framework."""

import os, shutil, string
from twisted.persisted import dirdbm, styles


class Entity(object):
    """A persistent object, stored in a dirdbm.

    Attributes are stored in memory, and changes written to disk.
    """

    # list of attributes entities of this class can have.
    # override in subclasses
    entityAttributes = ()
    
    def __init__(self, id, path):
        self._id = id
        self._dbm = dirdbm.Shelf(path)
        self._path = path
        for k in self.entityAttributes:
            if self._dbm.has_key(k): object.__setattr__(self, k, self._dbm[k])

    def __getstate__(self):
        return {'_id' : self._id, '_path': self._path}

    def __setstate__(self, state):
        self.__dict__ = state
        Entity.__init__(self, self._id, self._path)
    
    def __repr__(self):
        return "<%s with id %r>" % (self.__class__, self._id)

    def getId(self):
        """Return id."""
        return self._id

    def __setattr__(self, k, v):
        if k.startswith("_"):
            object.__setattr__(self, k, v)
        else:
            if k in self.entityAttributes:
                self._dbm[k] = v
                object.__setattr__(self, k, v)
            else:
                raise ValueError, "entities of type %s can't have attribute %s" % (self.__class__, k)


class Collection:
    """A collection of persistent objects, stored in dirdbms."""

    entityClass = Entity # class of objects to store
    
    def __init__(self, path):
        self.path = path
        self.entities = {} # name -> entity mapping
        for name in os.listdir(path):
            if not name[0] in (string.ascii_letters + string.digits):
                continue
            subPath = os.path.join(path, name)
            if os.path.isdir(subPath):
                self.entities[name] = self.entityClass(name, subPath)

    def __getstate__(self):
        return {'path' : self.path}

    def __setstate__(self, state):
        self.__init__(state['path'])
    
    def __getitem__(self, name):
        return self.entities[name]

    def createEntity(self, name, **kwargs):
        """Create new entity with given attributes and return it."""
        if self.entities.has_key(name):
            raise ValueError, "entity with name %r already exists" % name
        for k in kwargs.keys():
            if k not in self.entityClass.entityAttributes: raise ValueError
        e = self.entityClass(name, os.path.join(self.path, name))
        self.entities[name] = e
        for k, v in kwargs.items():
            setattr(e, k, v)
        return e

    def __delitem__(self, name):
        """Delete an entity."""
        del self.entities[name]
        shutil.rmtree(os.path.join(self.path, name))

    def keys(self):
        return self.entities.keys()

    def values(self):
        return self.entities.values()

    def items(self):
        return self.entities.items()
